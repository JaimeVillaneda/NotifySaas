<?php

namespace App\Filament\Resources\MessageDispatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MessageDispatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('automation.name')
                    ->label('Recordatorio')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('person.full_name')
                    ->label('Cliente')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('phone')
                    ->label('Destino')
                    ->searchable(),
                TextColumn::make('channel')
                    ->label('Canal')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('attempts')
                    ->label('Intentos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Programado')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Enviado')
                    ->dateTime()
                    ->sortable(),
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
                        'sent' => 'Enviado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
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
