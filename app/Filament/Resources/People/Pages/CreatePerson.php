<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Resources\People\PersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    /**
     * Assign new clients to the authenticated company.
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
