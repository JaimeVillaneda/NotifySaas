<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Company;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Empresa')
                    ->options(Company::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->maxLength(255)
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('Verificado en'),
                TextInput::make('password')
                    ->label('Clave')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Toggle::make('is_admin')
                    ->label('Puede entrar al panel')
                    ->default(true)
                    ->required(),
            ]);
    }
}
