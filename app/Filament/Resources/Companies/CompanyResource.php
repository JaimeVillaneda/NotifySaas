<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Companies\Tables\CompaniesTable;
use App\Models\Company;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Administracion';

    protected static ?string $modelLabel = 'empresa';

    protected static ?string $pluralModelLabel = 'empresas';

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }
    
/**
 * Restrict company records to the authenticated company.
 */
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    if (! $user || ! $user->is_admin) {
        return $query->whereRaw('1 = 0');
    }

    // The initial platform administrator can access all companies.
    if ($user->company_id === null) {
        return $query;
    }

    // Company administrators can only access their own company.
    return $query->whereKey($user->company_id);
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
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
