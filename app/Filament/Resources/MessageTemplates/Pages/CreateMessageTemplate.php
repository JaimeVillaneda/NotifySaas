<?php

namespace App\Filament\Resources\MessageTemplates\Pages;

use App\Filament\Resources\MessageTemplates\MessageTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessageTemplate extends CreateRecord
{
    protected static string $resource = MessageTemplateResource::class;

    /**
     * Assign new message templates to the authenticated company.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        abort_unless($user && $user->is_admin, 403);

        if ($user->company_id !== null) {
            $data['company_id'] = $user->company_id;
        }

        return $data;
    }
}
