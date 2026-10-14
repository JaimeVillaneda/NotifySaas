<?php

namespace App\Filament\Resources\MessageAutomations\Schemas;

use App\Models\CommunicationConnection;
use App\Models\Company;
use App\Models\MessageTemplate;
use App\Models\Person;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MessageAutomationForm
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
                    ->live()
                    ->required(),
                Select::make('message_template_id')
                    ->label('Plantilla')
                    ->options(fn ($get) => MessageTemplate::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(255)
                    ->required(),
                Select::make('channel')
                    ->label('Canal')
                    ->options([
                        'telegram' => 'Telegram',
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->default('whatsapp')
                    ->required()
                    ->live(),
                Select::make('communication_connection_id')
                    ->label('Conexion')
                    ->options(fn ($get) => CommunicationConnection::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->when($get('channel'), fn ($query, $channel) => $query->where('channel', $channel))
                        ->where('is_enabled', true)
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Select::make('person_id')
                    ->label('Destinatario')
                    ->helperText('Dejalo vacio para enviar a todos los clientes activos de la empresa.')
                    ->options(fn ($get) => Person::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->where('is_active', true)
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn (Person $person): array => [$person->id => $person->full_name ?: $person->phone]))
                    ->searchable()
                    ->preload(),
                Select::make('frequency')
                    ->label('Frecuencia')
                    ->options([
                        'once' => 'Una vez',
                        'daily' => 'Diario',
                        'weekly' => 'Semanal',
                        'interval' => 'Cada intervalo',
                    ])
                    ->required()
                    ->default('once'),
                TimePicker::make('send_time')
                    ->label('Hora'),
                DatePicker::make('start_date')
                    ->label('Fecha inicio'),
                DatePicker::make('end_date')
                    ->label('Fecha fin'),
                TextInput::make('interval_value')
                    ->label('Enviar cada')
                    ->numeric(),
                Select::make('interval_unit')
                    ->label('Unidad')
                    ->options([
                        'minutes' => 'Minutos',
                        'hours' => 'Horas',
                        'days' => 'Dias',
                    ]),
                Textarea::make('week_days')
                    ->label('Dias de semana')
                    ->helperText('Opcional: guarda valores como 1,2,3 para lunes, martes, miercoles.')
                    ->columnSpanFull(),
                DateTimePicker::make('next_run_at')
                    ->label('Proxima ejecucion'),
                DateTimePicker::make('last_run_at')
                    ->label('Ultima ejecucion')
                    ->disabled(),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true)
                    ->required(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'running' => 'En proceso',
                        'paused' => 'Pausada',
                        'completed' => 'Completada',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }
}
