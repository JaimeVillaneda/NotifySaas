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
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * US-03: Mostrar solamente los envios
     * pertenecientes a la empresa autenticada.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Obtener el usuario autenticado.
        $user = auth()->user();

        // Bloquear usuarios no autorizados.
        if (! $user || ! $user->is_admin) {
            return $query->whereRaw('1 = 0');
        }

        // Administrador general temporal.
        if ($user->company_id === null) {
            return $query;
        }

        // Filtrar por la empresa del usuario.
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
            'index' => ListMessageDispatches::route('/'),
            'edit' => EditMessageDispatch::route('/{record}/edit'),
        ];
    }
}
