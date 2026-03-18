<?php

namespace App\Filament\Resources\VotacionResource\RelationManagers;

use App\Models\Aportante;
use App\Models\VotacionVoto;
use Filament\Notifications\Notification;
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
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->state(function (VotacionVoto $record): string {
                        if ($record->voto_emitido_at) return 'Voto';
                        if ($record->acepto_orden_dia_at) return 'Orden aceptado';
                        return 'Pendiente';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Voto' => 'success',
                        'Orden aceptado' => 'warning',
                        'Pendiente' => 'danger',
                    }),
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
            ->filters([
                Tables\Filters\TernaryFilter::make('voto_emitido_at')
                    ->label('Filtrar por votación')
                    ->placeholder('Todos')
                    ->trueLabel('Ya votaron')
                    ->falseLabel('Faltan por votar')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('acepto_orden_dia_at')
                    ->label('Filtrar por orden del día')
                    ->placeholder('Todos')
                    ->trueLabel('Aceptaron')
                    ->falseLabel('No han aceptado')
                    ->nullable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_agenda')
                    ->label('Descargar Excel (Aceptación)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (RelationManager $livewire) {
                        $votacion = $livewire->getOwnerRecord();
                        $votos = $votacion->votos()->with('aportante')->get();
                        
                        $csvData = "Documento;Nombre;Aceptacion Orden Dia At\n";
                        foreach ($votos as $voto) {
                            $fecha = $voto->acepto_orden_dia_at ? $voto->acepto_orden_dia_at->format('d/m/Y H:i') : 'Pendiente';
                            $csvData .= "{$voto->aportante->documento};{$voto->aportante->nombre};{$fecha}\n";
                        }
                        
                        return response()->streamDownload(function () use ($csvData) {
                            echo "\xEF\xBB\xBF"; // UTF-8 BOM
                            echo $csvData;
                        }, "aceptacion_orden_dia_{$votacion->slug}.csv");
                    }),
                Tables\Actions\Action::make('sync_participantes')
                    ->label('Cargar Todos los Pendientes')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->action(function (RelationManager $livewire) {
                        $record = $livewire->getOwnerRecord();
                        $participantes = Aportante::where('activo', true)->get();
                        $registrados = 0;
                        
                        foreach ($participantes as $participante) {
                            $voto = VotacionVoto::firstOrCreate([
                                'votacion_id' => $record->id,
                                'aportante_id' => $participante->id,
                            ]);
                            if ($voto->wasRecentlyCreated) $registrados++;
                        }
                        
                        Notification::make()
                            ->title('Sincronización completa')
                            ->body("Se han habilitado {$registrados} nuevos participantes para esta votación.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Cargar a todos los aportantes habilitados?')
                    ->modalDescription('Esto creará un registro para cada aportante activo para que puedas ver quién no ha votado aún.')
                    ->modalSubmitActionLabel('Cargar ahora'),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
