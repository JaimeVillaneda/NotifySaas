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
     * Busca recordatorios pendientes y crea registros de envio por cliente.
     *
     * Este comando no envia todavia el mensaje al proveedor externo; deja los
     * envios en historial con estado pending para que el siguiente paso pueda
     * procesarlos por Telegram, SMTP o Evolution API.
     */
    public function handle(): int
    {
        $now = now();
        $processed = 0;

        MessageAutomation::query()
            ->with(['template', 'connection'])
            ->where('is_active', true)
            ->where('status', 'pending')
            ->where(function ($query) use ($now): void {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', $now);
            })
            ->chunkById(50, function ($automations) use (&$processed, $now): void {
                foreach ($automations as $automation) {
                    Person::query()
                        ->where('is_active', true)
                        ->when($automation->company_id, fn ($query) => $query->where('company_id', $automation->company_id))
                        ->when($automation->person_id, fn ($query) => $query->where('id', $automation->person_id))
                        ->chunkById(100, function ($people) use ($automation, &$processed, $now): void {
                            foreach ($people as $person) {
                                $automation->dispatches()->create([
                                    'company_id' => $automation->company_id,
                                    'message_template_id' => $automation->message_template_id,
                                    'communication_connection_id' => $automation->communication_connection_id,
                                    'person_id' => $person->id,
                                    'phone' => $person->phone,
                                    'channel' => $automation->channel,
                                    'message' => $this->renderMessage($automation->template->body, $person),
                                    'status' => 'pending',
                                    'scheduled_at' => $now,
                                ]);

                                $processed++;
                            }
                        });

                    $automation->forceFill([
                        'last_run_at' => $now,
                        'next_run_at' => $this->nextRunAt($automation, $now),
                        'status' => $automation->frequency === 'once' ? 'completed' : 'pending',
                    ])->save();
                }
            });

        $this->info("Dispatches generated: {$processed}");

        return self::SUCCESS;
    }

    /**
     * Reemplaza variables simples de la plantilla con datos del cliente.
     */
    private function renderMessage(string $template, Person $person): string
    {
        return str($template)
            ->replace('{{ nombre }}', $person->full_name)
            ->replace('{{ telefono }}', $person->phone)
            ->replace('{{ email }}', (string) $person->email)
            ->toString();
    }

    /**
     * Calcula la siguiente ejecucion segun la frecuencia configurada.
     */
    private function nextRunAt(MessageAutomation $automation, Carbon $from): ?Carbon
    {
        return match ($automation->frequency) {
            'daily' => $from->copy()->addDay(),
            'weekly' => $from->copy()->addWeek(),
            'interval' => $from->copy()->add(
                $automation->interval_unit ?: 'days',
                $automation->interval_value ?: 1,
            ),
            default => null,
        };
    }
}
