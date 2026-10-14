<?php

namespace App\Filament\Resources\MessageDispatches\Schemas;

use App\Models\CommunicationConnection;
use App\Models\Company;
use App\Models\MessageAutomation;
use App\Models\MessageTemplate;
use App\Models\Person;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MessageDispatchForm
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
                    ->live(),
                Select::make('message_automation_id')
                    ->label('Recordatorio')
                    ->options(fn ($get) => MessageAutomation::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Select::make('message_template_id')
                    ->label('Plantilla')
                    ->options(fn ($get) => MessageTemplate::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Select::make('communication_connection_id')
                    ->label('Conexion')
                    ->options(fn ($get) => CommunicationConnection::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Select::make('person_id')
                    ->label('Cliente')
                    ->options(fn ($get) => Person::query()
                        ->when($get('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn (Person $person): array => [$person->id => $person->full_name ?: $person->phone]))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('phone')
                    ->label('Destino')
                    ->tel()
                    ->required(),
                Select::make('channel')
                    ->label('Canal')
                    ->options([
                        'telegram' => 'Telegram',
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ])
                    ->default('whatsapp')
                    ->required(),
                Textarea::make('message')
                    ->label('Mensaje')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'sent' => 'Enviado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                    ])
                    ->required()
                    ->default('pending'),
                TextInput::make('attempts')
                    ->label('Intentos')
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('scheduled_at')
                    ->label('Programado para'),
                DateTimePicker::make('sent_at')
                    ->label('Enviado en'),
                Textarea::make('response')
                    ->label('Respuesta')
                    ->columnSpanFull(),
                Textarea::make('error')
                    ->label('Error')
                    ->columnSpanFull(),
            ]);
    }
}
