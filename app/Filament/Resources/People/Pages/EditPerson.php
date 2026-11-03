<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Resources\People\PersonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    /**
     * Keep the client assigned to its authorized company.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        $person = $this->getRecord();

        abort_unless($user && $user->is_admin, 403);

        // Temporary platform administrator.
        if ($user->company_id === null) {
            return $data;
        }

        // Prevent editing clients from another company.
        abort_unless(
            (int) $person->company_id === (int) $user->company_id,
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
