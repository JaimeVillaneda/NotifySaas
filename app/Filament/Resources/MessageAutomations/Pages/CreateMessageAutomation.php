<?php

namespace App\Filament\Resources\MessageAutomations\Pages;

use App\Filament\Resources\MessageAutomations\MessageAutomationResource;
use App\Models\CommunicationConnection;
use App\Models\MessageTemplate;
use App\Models\Person;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateMessageAutomation extends CreateRecord
{
    protected static string $resource = MessageAutomationResource::class;

    /**
     * Validate tenant ownership before creating a reminder.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        abort_unless($user && $user->is_admin, 403);

        // Assign the authenticated company automatically.
        if ($user->company_id !== null) {
            $data['company_id'] = $user->company_id;
        }

        $companyId = $data['company_id'] ?? null;

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'A valid company is required.',
            ]);
        }

        // The selected template must belong to the same company.
        if (! MessageTemplate::query()
            ->whereKey($data['message_template_id'] ?? null)
            ->where('company_id', $companyId)
            ->exists()) {
            throw ValidationException::withMessages([
                'message_template_id' => 'The template does not belong to this company.',
            ]);
        }

        // The recipient, when selected, must belong to the company.
        if (! empty($data['person_id']) && ! Person::query()
            ->whereKey($data['person_id'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists()) {
            throw ValidationException::withMessages([
                'person_id' => 'The recipient does not belong to this company.',
            ]);
        }

        // The connection must belong to the company and channel.
        if (! empty($data['communication_connection_id'])
            && ! CommunicationConnection::query()
                ->whereKey($data['communication_connection_id'])
                ->where('company_id', $companyId)
                ->where('channel', $data['channel'] ?? '')
                ->where('is_enabled', true)
                ->exists()) {
            throw ValidationException::withMessages([
                'communication_connection_id' => 'Invalid connection for this company.',
            ]);
        }

        return $data;
    }
}
