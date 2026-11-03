<?php

namespace App\Filament\Resources\MessageTemplates\Pages;

use App\Filament\Resources\MessageTemplates\MessageTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessageTemplate extends EditRecord
{
    protected static string $resource = MessageTemplateResource::class;

    /**
     * Keep templates assigned to their authorized company.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        $template = $this->getRecord();

        abort_unless($user && $user->is_admin, 403);

        // Temporary platform administrator.
        if ($user->company_id === null) {
            return $data;
        }

        // Prevent editing templates from another company.
        abort_unless(
            (int) $template->company_id === (int) $user->company_id,
            403
        );

        // Preserve the correct company association.
        $data['company_id'] = $user->company_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
