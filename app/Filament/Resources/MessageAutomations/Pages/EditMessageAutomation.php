<?php

namespace App\Filament\Resources\MessageAutomations\Pages;

use App\Filament\Resources\MessageAutomations\MessageAutomationResource;
use App\Models\CommunicationConnection;
use App\Models\MessageTemplate;
use App\Models\Person;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditMessageAutomation extends EditRecord
{
    protected static string $resource = MessageAutomationResource::class;

    /**
     * Validate company ownership and related records before saving.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        $reminder = $this->getRecord();

        abort_unless($user && $user->is_admin, 403);

        // Check that the user can edit this reminder.
        if ($user->company_id !== null) {
            abort_unless(
                (int) $reminder->company_id === (int) $user->company_id,
                403
            );
        }

        // Prevent moving the reminder to another company.
        $companyId = $reminder->company_id;
        abort_unless($companyId !== null, 403);

        $data['company_id'] = $companyId;

        // Validate the selected message template.
        $templateId = $data['message_template_id']
            ?? $reminder->message_template_id;

        if (! MessageTemplate::query()
            ->whereKey($templateId)
            ->where('company_id', $companyId)
            ->exists()) {
            throw ValidationException::withMessages([
                'message_template_id' => 'La plantilla no pertenece a esta empresa.',
            ]);
        }

        // Validate the selected recipient.
        $personId = array_key_exists('person_id', $data)
            ? $data['person_id']
            : $reminder->person_id;

        if (! empty($personId) && ! Person::query()
            ->whereKey($personId)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'person_id' => 'El destinatario no pertenece a esta empresa.',
            ]);
        }

        // Validate the selected communication connection.
        $connectionId = array_key_exists(
            'communication_connection_id',
            $data
        ) ? $data['communication_connection_id']
          : $reminder->communication_connection_id;

        $channel = $data['channel'] ?? $reminder->channel;

        if (! empty($connectionId) && ! CommunicationConnection::query()
            ->whereKey($connectionId)
            ->where('company_id', $companyId)
            ->where('channel', $channel)
            ->where('is_enabled', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'communication_connection_id' => 'La conexion no es valida para esta empresa.',
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
