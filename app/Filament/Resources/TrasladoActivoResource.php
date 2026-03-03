<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrasladoActivoResource\Pages;
use App\Models\ActivoFijoResponsableHistorial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrasladoActivoResource extends Resource
{
    protected static ?string $model = ActivoFijoResponsableHistorial::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationGroup = 'Gestion';
    protected static ?string $navigationLabel = 'Traslados Activos';
    protected static ?string $modelLabel = 'Traslado Activo';
    protected static ?string $pluralModelLabel = 'Traslados Activos';
    protected static ?int $navigationSort = 22;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('changed_at')
                    ->label('Fecha Cambio')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activo.codigo')
                    ->label('Codigo Activo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activo.descripcion')
                    ->label('Activo')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('usuarioAnterior.name')
                    ->label('Usuario Anterior')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usuarioNuevo.name')
                    ->label('Usuario Nuevo')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Cambiado Por')
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')
                    ->default('-')
                    ->wrap()
                    ->limit(80),
            ])
            ->defaultSort('changed_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('rango_fecha')
                    ->label('Rango Fecha')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'] ?? null,
                                fn(Builder $q, $date): Builder => $q->whereDate('changed_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'] ?? null,
                                fn(Builder $q, $date): Builder => $q->whereDate('changed_at', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('usuario_nuevo_id')
                    ->label('Usuario Nuevo')
                    ->relationship('usuarioNuevo', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('changed_by_user_id')
                    ->label('Cambiado Por')
                    ->relationship('changedBy', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrasladoActivos::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'activo',
                'usuarioAnterior',
                'usuarioNuevo',
                'changedBy',
            ]);
    }
}

