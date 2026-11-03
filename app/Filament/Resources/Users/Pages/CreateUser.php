<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Assign new users to the authenticated company.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        abort_unless($user, 403);

        if ($user->company_id !== null) {
            $data['company_id'] = $user->company_id;
        }

        return $data;
    }
}
