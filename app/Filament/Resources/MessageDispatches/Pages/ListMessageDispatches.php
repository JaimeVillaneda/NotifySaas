<?php

namespace App\Filament\Resources\MessageDispatches\Pages;

use App\Filament\Resources\MessageDispatches\MessageDispatchResource;
use Filament\Resources\Pages\ListRecords;

class ListMessageDispatches extends ListRecords
{
    protected static string $resource = MessageDispatchResource::class;
}
