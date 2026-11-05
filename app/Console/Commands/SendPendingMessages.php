<?php

namespace App\Console\Commands;

use App\Models\MessageDispatch;
use App\Services\TelegramDeliveryService;
use App\Services\EmailDeliveryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('messages:send')]
#[Description('Send pending Email and Telegram messages.')]
class SendPendingMessages extends Command
{
    /**
     * Procesa mensajes pendientes de Telegram y Email.
     *
     * Actualiza el historial con el resultado del envio.
     */
    public function handle(
        TelegramDeliveryService $telegram,
        EmailDeliveryService $email
    ): int {

        $sent = 0;
        $failed = 0;

        /**
         * PASO 1:
         * Buscar mensajes pendientes de los canales
         * que actualmente tenemos implementados.
         */
        MessageDispatch::query()
            ->where('status', 'pending')
            ->whereIn('channel', [
                'telegram',
                'email',
            ])
            ->whereNotNull('company_id')
            ->whereHas('company', function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->chunkById(
                100,
                function ($dispatches) use (
                    $telegram,
                    $email,
                    &$sent,
                    &$failed
                ): void {

                    foreach ($dispatches as $dispatch) {

                        /**
                         * PASO 2:
                         * Reclamar el mensaje antes de enviarlo.
                         */
                        $claimed = MessageDispatch::query()
                            ->whereKey($dispatch->id)
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'processing',
                                'attempts' => DB::raw(
                                    'attempts + 1'
                                ),
                            ]);

                        // Otro proceso ya lo reclamo.
                        if ($claimed !== 1) {
                            continue;
                        }

                        $dispatch->refresh();

                        try {

                            /**
                             * PASO 3:
                             * Seleccionar el servicio segun
                             * el canal del mensaje.
                             */
                            if ($dispatch->channel === 'telegram') {

                                $messageId = $telegram->send(
                                    $dispatch
                                );

                                $response = 'Telegram message ID: '
                                    . $messageId;

                            } elseif ($dispatch->channel === 'email') {

                                $email->send($dispatch);

                                $response = 'Mensaje aceptado '
                                    . 'por el servidor SMTP.';

                            } else {

                                throw new \RuntimeException(
                                    'Canal no soportado.'
                                );
                            }

                            /**
                             * PASO 4:
                             * Registrar el envio exitoso.
                             */
                            $dispatch->forceFill([
                                'status' => 'sent',
                                'sent_at' => now(),
                                'response' => $response,
                                'error' => null,
                            ])->save();

                            $sent++;

                            $this->info(
                                "Envio {$dispatch->id} "
                                . "({$dispatch->channel}): enviado."
                            );

                        } catch (Throwable $exception) {

                            /**
                             * PASO 5:
                             * Registrar el error.
                             *
                             * No interrumpir los demas envios.
                             */
                            $dispatch->forceFill([
                                'status' => 'failed',
                                'sent_at' => null,
                                'response' => null,
                                'error' => 'No se pudo procesar '
                                    . 'el mensaje. Verifique '
                                    . 'la configuracion del canal '
                                    . 'y los datos del destinatario.',
                            ])->save();

                            report($exception);

                            $failed++;

                            $this->error(
                                "Envio {$dispatch->id} "
                                . "({$dispatch->channel}): fallido."
                            );
                        }
                    }
                }
            );

        /**
         * PASO 6:
         * Mostrar resumen de resultados.
         */
        $this->info("Enviados: {$sent}");
        $this->info("Fallidos: {$failed}");

        return self::SUCCESS;
    }
}
