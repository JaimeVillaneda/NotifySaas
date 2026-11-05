<?php

namespace App\Console\Commands;

use App\Models\MessageAutomation;
use App\Models\Person;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

#[Signature('reminders:process')]
#[Description('Process scheduled reminders and generate dispatch records.')]
class ProcessPendingReminders extends Command
{

public function handle(): int
{
    // Bloquear ejecuciones simultaneas.
    // El bloqueo caduca como maximo en 10 minutos.
    $lock = Cache::lock(
        'notifysaas:reminders:process',
        600
    );

    // Si otra ejecucion tiene el bloqueo,
    // no volver a procesar los recordatorios.
    if (! $lock->get()) {
        $this->warn(
            'Ya existe un procesamiento de recordatorios en curso.'
        );

        return self::SUCCESS;
    }

    try {
        // Ejecutar el procesamiento protegido.
        return $this->processReminders();

    } finally {
        // Liberar el bloqueo incluso si ocurre un error.
        $lock->release();
    }
}

/**
 * Procesamiento de recordatorios.
 *
 * Conserva las validaciones de empresas,
 * clientes, plantillas, fechas y horarios.
 */
private function processReminders(): int
{

        $now = now();
        $processed = 0;

        // Buscar recordatorios pendientes de empresas activas.
        MessageAutomation::query()
            ->with(['template', 'connection'])
            ->where('is_active', true)
            ->where('status', 'pending')
            ->whereNotNull('company_id')
            ->whereHas('company', function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', $now);
            })
            ->chunkById(50, function ($automations) use (
                &$processed,
                $now
            ): void {

                foreach ($automations as $automation) {

                    // 1. No ejecutar antes de la fecha inicial.
                    if (
                        $automation->start_date &&
                        $automation->start_date
                            ->copy()
                            ->startOfDay()
                            ->gt($now)
                    ) {
                        continue;
                    }

                    // 2. Finalizar recordatorios vencidos.
                    if (
                        $automation->end_date &&
                        $automation->end_date
                            ->copy()
                            ->endOfDay()
                            ->lt($now)
                    ) {
                        $automation->forceFill([
                            'status' => 'completed',
                            'next_run_at' => null,
                        ])->save();

                        continue;
                    }

                    // 3. Respetar la hora de envio.
                    if ($automation->send_time) {
                        $sendAtToday = $now
                            ->copy()
                            ->startOfDay()
                            ->setTimeFromTimeString(
                                $automation->send_time
                            );

                        if ($now->lt($sendAtToday)) {
                            continue;
                        }
                    }

                    // 4. Verificar plantilla y empresa.
                    if (
                        ! $automation->template ||
                        ! $automation->template->is_active ||
                        (int) $automation->template->company_id
                            !== (int) $automation->company_id
                    ) {
                        $this->warn(
                            "Recordatorio {$automation->id}: "
                            . "plantilla no valida."
                        );

                        continue;
                    }

                    // 5. Seleccionar clientes autorizados.
                    Person::query()
                        ->where('is_active', true)
                        ->where(
                            'company_id',
                            $automation->company_id
                        )
                        ->when(
                            $automation->person_id,
                            fn ($query) => $query->where(
                                'id',
                                $automation->person_id
                            )
                        )
                        ->chunkById(100, function ($people) use (
                            $automation,
                            $now,
                            &$processed
                        ): void {

                            foreach ($people as $person) {

                                // Crear el registro pendiente.
                                $automation->dispatches()->create([
                                    'company_id' =>
                                        $automation->company_id,

                                    'message_template_id' =>
                                        $automation->message_template_id,

                                    'communication_connection_id' =>
                                        $automation->communication_connection_id,

                                    'person_id' => $person->id,
                                    'phone' => $person->phone,
                                    'channel' => $automation->channel,

                                    'message' => $this->renderMessage(
                                        $automation->template->body,
                                        $person
                                    ),

                                    'status' => 'pending',
                                    'scheduled_at' => $now,
                                ]);

                                $processed++;
                            }
                        });

                    // 6. Calcular proxima ejecucion.
                    $next = $this->nextRunAt(
                        $automation,
                        $now
                    );

                    // 7. Verificar si el recordatorio termina.
                    $finished = $automation->frequency === 'once';

                    if (
                        $automation->end_date &&
                        $next &&
                        $next->gt(
                            $automation->end_date
                                ->copy()
                                ->endOfDay()
                        )
                    ) {
                        $finished = true;
                    }

                    // 8. Actualizar estado y programacion.
                    $automation->forceFill([
                        'last_run_at' => $now,
                        'next_run_at' => $finished
                            ? null
                            : $next,
                        'status' => $finished
                            ? 'completed'
                            : 'pending',
                    ])->save();
                }
            });

        $this->info("Dispatches generated: {$processed}");

        return self::SUCCESS;
    }

    /**
     * Reemplazar variables con datos del cliente.
     */
    private function renderMessage(
        string $template,
        Person $person
    ): string {
        return str($template)
            ->replace('{{ nombre }}', $person->full_name)
            ->replace('{{ telefono }}', $person->phone)
            ->replace('{{ email }}', (string) $person->email)
            ->toString();
    }

    /**
     * Calcular la proxima ejecucion del recordatorio.
     */
    private function nextRunAt(
        MessageAutomation $automation,
        Carbon $from
    ): ?Carbon {
        $next = match ($automation->frequency) {
            'daily' => $from->copy()->addDay(),

            'weekly' => $from->copy()->addWeek(),

            'interval' => $from->copy()->add(
                in_array(
                    $automation->interval_unit,
                    ['minutes', 'hours', 'days'],
                    true
                )
                    ? $automation->interval_unit
                    : 'days',
                max(1, (int) $automation->interval_value)
            ),

            default => null,
        };

        // Mantener la hora configurada para
        // recordatorios diarios y semanales.
        if (
            $next &&
            $automation->send_time &&
            in_array(
                $automation->frequency,
                ['daily', 'weekly'],
                true
            )
        ) {
            $next->setTimeFromTimeString(
                $automation->send_time
            );
        }

        return $next;
    }
}
