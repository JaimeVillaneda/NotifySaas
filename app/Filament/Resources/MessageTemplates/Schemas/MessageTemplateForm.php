<?php

namespace App\Filament\Resources\MessageTemplates\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MessageTemplateForm
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
                TextInput::make('slug')
                    ->label('Codigo')
                    ->helperText('Identificador corto. Ejemplo: cita-manana')
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('Mensaje')
                    ->helperText('Variables sugeridas: {{ nombre }}, {{ telefono }}, {{ email }}.')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true)
                    ->required(),
                Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
            ]);
    }
}
