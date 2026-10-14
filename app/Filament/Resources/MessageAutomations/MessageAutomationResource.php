<?php

namespace App\Filament\Resources\MessageAutomations;

use App\Filament\Resources\MessageAutomations\Pages\CreateMessageAutomation;
use App\Filament\Resources\MessageAutomations\Pages\EditMessageAutomation;
use App\Filament\Resources\MessageAutomations\Pages\ListMessageAutomations;
use App\Filament\Resources\MessageAutomations\Schemas\MessageAutomationForm;
use App\Filament\Resources\MessageAutomations\Tables\MessageAutomationsTable;
use App\Models\MessageAutomation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MessageAutomationResource extends Resource
{
    protected static ?string $model = MessageAutomation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Mensajeria';

    protected static ?string $modelLabel = 'recordatorio';

    protected static ?string $pluralModelLabel = 'recordatorios';

    public static function form(Schema $schema): Schema
    {
        return MessageAutomationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageAutomationsTable::configure($table);
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
            'index' => ListMessageAutomations::route('/'),
            'create' => CreateMessageAutomation::route('/create'),
            'edit' => EditMessageAutomation::route('/{record}/edit'),
        ];
    }
}
