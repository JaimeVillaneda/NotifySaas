<?php

namespace App\Filament\Resources\People\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PersonForm
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
                TextInput::make('first_name')
                    ->label('Nombre')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('last_name')
                    ->label('Apellido')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefono WhatsApp')
                    ->tel()
                    ->maxLength(40)
                    ->required(),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->maxLength(255),
                Select::make('preferred_channel')
                    ->label('Canal preferido')
                    ->options([
                        'telegram' => 'Telegram',
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->default('whatsapp')
                    ->required(),
                TextInput::make('telegram_chat_id')
                    ->label('Telegram Chat ID')
                    ->helperText('Necesario para enviar mensajes directos por Telegram.')
                    ->maxLength(255),
                TextInput::make('document')
                    ->label('Documento')
                    ->maxLength(80),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
