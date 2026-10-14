<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefono')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('tax_id')
                    ->label('Documento / NIT')
                    ->maxLength(255),
                Textarea::make('address')
                    ->label('Direccion')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true)
                    ->required(),
            ]);
    }
}
