<?php

namespace App\Filament\Resources\MessageAutomations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessageAutomationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Recordatorio')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Plantilla')
                    ->searchable(),
                TextColumn::make('person.full_name')
                    ->label('Destinatario')
                    ->placeholder('Todos')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('channel')
                    ->label('Canal')
                    ->badge(),
                TextColumn::make('frequency')
                    ->label('Frecuencia')
                    ->badge()
                    ->searchable(),
                TextColumn::make('send_time')
                    ->label('Hora')
                    ->time()
                    ->sortable(),
                TextColumn::make('next_run_at')
                    ->label('Proxima')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name'),
                SelectFilter::make('channel')
                    ->label('Canal')
                    ->options([
                        'telegram' => 'Telegram',
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ]),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'running' => 'En proceso',
                        'paused' => 'Pausada',
                        'completed' => 'Completada',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
