<?php

namespace App\Filament\Resources\MessageDispatches;

use App\Filament\Resources\MessageDispatches\Pages\EditMessageDispatch;
use App\Filament\Resources\MessageDispatches\Pages\ListMessageDispatches;
use App\Filament\Resources\MessageDispatches\Schemas\MessageDispatchForm;
use App\Filament\Resources\MessageDispatches\Tables\MessageDispatchesTable;
use App\Models\MessageDispatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MessageDispatchResource extends Resource
{
    protected static ?string $model = MessageDispatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|UnitEnum|null $navigationGroup = 'Mensajeria';

    protected static ?string $modelLabel = 'envio';

    protected static ?string $pluralModelLabel = 'historial de envios';

    public static function form(Schema $schema): Schema
    {
        return MessageDispatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageDispatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageDispatches::route('/'),
            'edit' => EditMessageDispatch::route('/{record}/edit'),
        ];
    }
}
