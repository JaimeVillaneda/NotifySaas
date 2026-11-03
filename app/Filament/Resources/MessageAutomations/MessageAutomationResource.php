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
use Illuminate\Database\Eloquent\Builder;

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
    
    /**
     * Restrict reminders to the authenticated company.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || ! $user->is_admin) {
            return $query->whereRaw('1 = 0');
        }

        // Temporary platform administrator.
        if ($user->company_id === null) {
            return $query;
        }

        // Show only reminders from the authenticated company.
        return $query->where(
            'company_id',
            $user->company_id
        );
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
