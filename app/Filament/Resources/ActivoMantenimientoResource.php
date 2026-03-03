<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivoMantenimientoResource\Pages;
use App\Models\ActivoFijo;
use App\Models\ActivoMantenimiento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivoMantenimientoResource extends Resource
{
    protected static ?string $model = ActivoMantenimiento::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Gestion';
    protected static ?string $navigationLabel = 'Mantenimientos Activos';
    protected static ?string $modelLabel = 'Mantenimiento Activo';
    protected static ?string $pluralModelLabel = 'Mantenimientos Activos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('equipo_id')
                    ->label('Activo')
                    ->options(fn() => ActivoFijo::query()
                        ->orderBy('descripcion')
                        ->get()
                        ->mapWithKeys(fn(ActivoFijo $activo) => [
                            $activo->id => trim(($activo->codigo ? $activo->codigo . ' - ' : '') . $activo->descripcion),
                        ])
                        ->all())
                    ->default(fn() => request()->integer('equipo_id') ?: null)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('tipo_M')
                    ->label('Tipo Mantenimiento')
                    ->options(self::tipoMantenimientoOptions())
                    ->required(),
                Forms\Components\Textarea::make('observacion_M')
                    ->label('Actividad')
                    ->rows(5)
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipo.codigo')
                    ->label('Codigo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('equipo.descripcion')
                    ->label('Activo')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('equipo.tipoActivo.tipo')
                    ->label('Tipo Activo')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('equipo.sede.NombreSede')
                    ->label('Oficina')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tipo_M')
                    ->label('Tipo Mantenimiento')
                    ->formatStateUsing(fn($state): string => self::tipoMantenimientoOptions()[$state] ?? (string) $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('observacion_M')
                    ->label('Actividad')
                    ->limit(80)
                    ->wrap(),
                Tables\Columns\TextColumn::make('creador.name')
                    ->label('Usuario')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fecadi')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_M')
                    ->label('Tipo Mantenimiento')
                    ->options(self::tipoMantenimientoOptions()),
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivoMantenimientos::route('/'),
            'create' => Pages\CreateActivoMantenimiento::route('/create'),
            'edit' => Pages\EditActivoMantenimiento::route('/{record}/edit'),
        ];
    }

    public static function tipoMantenimientoOptions(): array
    {
        return [
            0 => 'Preventivo',
            1 => 'Correctivo',
            2 => 'Predictivo',
            3 => 'Software',
        ];
    }
}
