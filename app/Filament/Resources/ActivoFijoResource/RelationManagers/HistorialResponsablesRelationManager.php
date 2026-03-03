<?php

namespace App\Filament\Resources\ActivoFijoResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HistorialResponsablesRelationManager extends RelationManager
{
    protected static string $relationship = 'historialResponsables';

    protected static ?string $title = 'Trazabilidad de Responsables';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('changed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('changed_at')
                    ->label('Fecha Cambio')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('usuarioAnterior.name')
                    ->label('Usuario Anterior')
                    ->formatStateUsing(function ($state, $record): string {
                        $usuario = trim((string) $state);
                        if ($usuario !== '') {
                            return $usuario;
                        }

                        return (string) ($record->responsable_anterior ?? '-');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('usuarioNuevo.name')
                    ->label('Usuario Nuevo')
                    ->formatStateUsing(function ($state, $record): string {
                        $usuario = trim((string) $state);
                        if ($usuario !== '') {
                            return $usuario;
                        }

                        return (string) ($record->responsable_nuevo ?? '-');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Cambio Realizado Por')
                    ->default('-'),
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->default('-')
                    ->wrap(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}

