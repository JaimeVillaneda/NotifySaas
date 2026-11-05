<?php

namespace App\Console\Commands;

use App\Models\MessageAutomation;
use App\Models\Person;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('reminders:process')]
#[Description('Process pending message reminders and create dispatch records.')]
class ProcessPendingReminders extends Command
{
    /**
     * Busca recordatorios pendientes y genera
     * registros de envio para los clientes.
     *
     * US-03: Garantizar el aislamiento entre empresas.
     *
     * IMPORTANTE:
     * Este comando todavia NO envia mensajes reales.
     * Los registros quedan en estado pending para
     * que posteriormente sean enviados por Telegram,
     * SMTP o Evolution API.
     */
    public function handle(): int
    {
        // Obtener la fecha y hora actual.
        $now = now();

        // Contador de registros de envio generados.
        $processed = 0;

        /**
         * PASO 1:
         * Buscar recordatorios que cumplan:
         *
         * - Estar activos.
         * - Tener estado pendiente.
         * - Pertenecer a una empresa.
         * - Tener una empresa activa.
         * - Haber llegado a su fecha de ejecucion.
         */
        MessageAutomation::query()
            ->with(['template', 'connection'])
            ->where('is_active', true)
            ->where('status', 'pending')
            ->whereNotNull('company_id')
            ->whereHas('company', function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', $now);
            })
            ->chunkById(
                50,
                function ($automations) use (&$processed, $now): void {

                    /**
                     * PASO 2:
                     * Recorrer los recordatorios encontrados.
                     */
                    foreach ($automations as $automation) {

                        /**
                         * PASO 3:
                         * Validar que la plantilla:
                         *
                         * - Exista.
                         * - Este activa.
                         * - Pertenezca a la misma empresa.
                         */
                        if (
                            ! $automation->template ||
                            ! $automation->template->is_active ||
                            (int) $automation->template->company_id
                                !== (int) $automation->company_id
                        ) {
                            $this->warn(
                                "Recordatorio {$automation->id} omitido: plantilla no valida."
                            );

                            continue;
                        }

                        /**
                         * PASO 4:
                         * Buscar solamente clientes activos
                         * de la empresa del recordatorio.
                         *
                         * US-03:
                         * El filtro por empresa es obligatorio.
                         */
                        Person::query()
                            ->where('is_active', true)
                            ->where(
                                'company_id',
                                $automation->company_id
                            )
                            ->when(
                                $automation->person_id,
                                function ($query) use ($automation) {
                                    $query->where(
                                        'id',
                                        $automation->person_id
                                    );
                                }
                            )
                            ->chunkById(
                                100,
                                function ($people) use (
                                    $automation,
                                    &$processed,
                                    $now
                                ): void {

                                    /**
                                     * PASO 5:
                                     * Crear un registro de envio
                                     * por cada cliente autorizado.
                                     */
                                    foreach ($people as $person) {

                                        $automation->dispatches()->create([
                                            'company_id' =>
                                                $automation->company_id,

                                            'message_template_id' =>
                                                $automation->message_template_id,

                                            'communication_connection_id' =>
                                                $automation->communication_connection_id,

                                            'person_id' =>
                                                $person->id,

                                            'phone' =>
                                                $person->phone,

                                            'channel' =>
                                                $automation->channel,

                                            'message' =>
                                                $this->renderMessage(
                                                    $automation->template->body,
                                                    $person
                                                ),

                                            'status' =>
                                                'pending',

                                            'scheduled_at' =>
                                                $now,
                                        ]);

                                        // Aumentar contador.
                                        $processed++;
                                    }
                                }
                            );

                        /**
                         * PASO 6:
                         * Actualizar el recordatorio procesado.
                         *
                         * Si es de una sola vez:
                         * marcar como completado.
                         *
                         * Si es recurrente:
                         * calcular la proxima ejecucion.
                         */
                        $automation->forceFill([
                            'last_run_at' => $now,

                            'next_run_at' => $this->nextRunAt(
                                $automation,
                                $now
                            ),

                            'status' =>
                                $automation->frequency === 'once'
                                    ? 'completed'
                                    : 'pending',
                        ])->save();
                    }
                }
            );

        /**
         * PASO 7:
         * Mostrar el numero de envios generados.
         */
        $this->info(
            "Dispatches generated: {$processed}"
        );

        return self::SUCCESS;
    }

    /**
     * Reemplaza las variables de una plantilla
     * con la informacion del cliente.
     *
     * Variables disponibles:
     * {{ nombre }}
     * {{ telefono }}
     * {{ email }}
     */
    private function renderMessage(
        string $template,
        Person $person
    ): string {
        return str($template)
            ->replace(
                '{{ nombre }}',
                $person->full_name
            )
            ->replace(
                '{{ telefono }}',
                $person->phone
            )
            ->replace(
                '{{ email }}',
                (string) $person->email
            )
            ->toString();
    }

    /**
     * Calcula la proxima ejecucion segun
     * la frecuencia del recordatorio.
     */
    private function nextRunAt(
        MessageAutomation $automation,
        Carbon $from
    ): ?Carbon {
        return match ($automation->frequency) {

            // Ejecutar nuevamente al dia siguiente.
            'daily' => $from->copy()->addDay(),

            // Ejecutar nuevamente en una semana.
            'weekly' => $from->copy()->addWeek(),

            // Ejecutar segun intervalo configurado.
            'interval' => $from->copy()->add(
                $automation->interval_unit ?: 'days',
                $automation->interval_value ?: 1
            ),

            // Una sola ejecucion.
            default => null,
        };
    }
}
