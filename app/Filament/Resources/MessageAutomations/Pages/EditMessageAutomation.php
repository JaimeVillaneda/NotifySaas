<?php

namespace App\Filament\Resources\MessageAutomations\Pages;

use App\Filament\Resources\MessageAutomations\MessageAutomationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessageAutomation extends EditRecord
{
    protected static string $resource = MessageAutomationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
