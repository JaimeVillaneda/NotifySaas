<?php

namespace App\Filament\Resources\MessageAutomations\Pages;

use App\Filament\Resources\MessageAutomations\MessageAutomationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessageAutomations extends ListRecords
{
    protected static string $resource = MessageAutomationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
