<?php

namespace App\Filament\Resources\VotacionResource\RelationManagers;

use App\Models\VotacionVoto;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VotosRelationManager extends RelationManager
{
    protected static string $relationship = 'votos';

    protected static ?string $title = 'Participacion';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['aportante', 'planilla', 'detalles.candidato']))
            ->columns([
                Tables\Columns\TextColumn::make('aportante.nombre')
                    ->label('Aportante')
                    ->searchable(),
                Tables\Columns\TextColumn::make('aportante.documento')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('acepto_orden_dia_at')
                    ->label('Acepto orden del dia')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Pendiente'),
                Tables\Columns\TextColumn::make('voto_emitido_at')
                    ->label('Voto emitido')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sin votar'),
                Tables\Columns\TextColumn::make('seleccion')
                    ->label('Seleccion')
                    ->state(function (VotacionVoto $record): string {
                        if ($record->planilla) {
                            return $record->planilla->nombre;
                        }

                        return $record->detalles
                            ->pluck('candidato.nombre')
                            ->filter()
                            ->join(', ') ?: 'Sin seleccion';
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
