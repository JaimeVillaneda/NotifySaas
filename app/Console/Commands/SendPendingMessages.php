<?php

namespace App\Console\Commands;

use App\Models\MessageDispatch;
use App\Services\EmailDeliveryService;
use App\Services\TelegramDeliveryService;
use App\Services\WhatsAppDeliveryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('messages:send')]
#[Description('Send pending Email, Telegram and WhatsApp messages.')]
class SendPendingMessages extends Command
{
    /**
     * Procesa mensajes pendientes de:
     * - Telegram
     * - Email SMTP
     * - WhatsApp Evolution API
     *
     * Registra en el historial los resultados.
     */
    public function handle(
        TelegramDeliveryService $telegram,
        EmailDeliveryService $email,
        WhatsAppDeliveryService $whatsapp
    ): int {

        $sent = 0;
        $failed = 0;

        /**
         * PASO 1:
         * Buscar mensajes pendientes de los
         * canales que tenemos implementados.
         */
        MessageDispatch::query()
            ->where('status', 'pending')
            ->whereIn('channel', [
                'telegram',
                'email',
                'whatsapp',
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
                    $whatsapp,
                    &$sent,
                    &$failed
                ): void {

                    foreach ($dispatches as $dispatch) {

                        /**
                         * PASO 2:
                         * Reclamar el mensaje antes de enviarlo.
                         *
                         * Evita que dos procesos reclamen
                         * simultaneamente el mismo registro.
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

                        // Otro proceso ya reclamo el mensaje.
                        if ($claimed !== 1) {
                            continue;
                        }

                        $dispatch->refresh();

                        try {

                            /**
                             * PASO 3:
                             * Seleccionar el servicio de envio
                             * segun el canal del mensaje.
                             */

                            // CANAL 1: TELEGRAM
                            if ($dispatch->channel === 'telegram') {

                                $messageId = $telegram->send(
                                    $dispatch
                                );

                                $response = 'Telegram message ID: '
                                    . $messageId;

                            // CANAL 2: EMAIL SMTP
                            } elseif ($dispatch->channel === 'email') {

                                $email->send($dispatch);

                                $response = 'Mensaje aceptado '
                                    . 'por el servidor SMTP.';

                            // CANAL 3: WHATSAPP EVOLUTION API
                            } elseif ($dispatch->channel === 'whatsapp') {

                                $messageId = $whatsapp->send(
                                    $dispatch
                                );

                                $response = 'WhatsApp Evolution API message ID: '
                                    . $messageId;

                            // CANAL NO SOPORTADO
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
                             * Registrar el envio fallido.
                             *
                             * No interrumpir el procesamiento
                             * de los demas mensajes.
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
         * Mostrar el resumen del procesamiento.
         */
        $this->info("Enviados: {$sent}");
        $this->info("Fallidos: {$failed}");

        return self::SUCCESS;
    }
}
