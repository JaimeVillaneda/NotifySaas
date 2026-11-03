<?php

namespace App\Filament\Resources\MessageTemplates;

use App\Filament\Resources\MessageTemplates\Pages\CreateMessageTemplate;
use App\Filament\Resources\MessageTemplates\Pages\EditMessageTemplate;
use App\Filament\Resources\MessageTemplates\Pages\ListMessageTemplates;
use App\Filament\Resources\MessageTemplates\Schemas\MessageTemplateForm;
use App\Filament\Resources\MessageTemplates\Tables\MessageTemplatesTable;
use App\Models\MessageTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;

class MessageTemplateResource extends Resource
{
    protected static ?string $model = MessageTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Mensajeria';

    protected static ?string $modelLabel = 'plantilla';

    protected static ?string $pluralModelLabel = 'plantillas';

    public static function form(Schema $schema): Schema
    {
        return MessageTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageTemplatesTable::configure($table);
    }

    
/**
 * Restrict message templates to the authenticated company.
 */
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    if (! $user || ! $user->is_admin) {
        return $query->whereRaw('1 = 0');
    }

    // Temporary platform administrator can view all templates.
    if ($user->company_id === null) {
        return $query;
    }

    // Company administrators can only view their templates.
    return $query->where('company_id', $user->company_id);
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
            'index' => ListMessageTemplates::route('/'),
            'create' => CreateMessageTemplate::route('/create'),
            'edit' => EditMessageTemplate::route('/{record}/edit'),
        ];
    }
}
