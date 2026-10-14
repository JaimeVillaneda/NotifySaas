<?php

namespace App\Filament\Resources\MessageDispatches\Pages;

use App\Filament\Resources\MessageDispatches\MessageDispatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessageDispatch extends EditRecord
{
    protected static string $resource = MessageDispatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
