<?php

namespace App\Filament\Resources\ActivoFijoResource\RelationManagers;

use App\Filament\Resources\ActivoMantenimientoResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'mantenimientos';

    protected static ?string $title = 'Mantenimientos';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tipo_M')
                ->label('Tipo Mantenimiento')
                ->options(ActivoMantenimientoResource::tipoMantenimientoOptions())
                ->required(),
            Forms\Components\Textarea::make('observacion_M')
                ->label('Actividad')
                ->rows(4)
                ->required()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('activo')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo_M')
                    ->label('Tipo')
                    ->formatStateUsing(fn($state): string => ActivoMantenimientoResource::tipoMantenimientoOptions()[$state] ?? (string) $state),
                Tables\Columns\TextColumn::make('observacion_M')
                    ->label('Actividad')
                    ->wrap()
                    ->limit(80),
                Tables\Columns\TextColumn::make('creador.name')
                    ->label('Usuario'),
                Tables\Columns\TextColumn::make('fecadi')
                    ->label('Fecha')
                    ->date(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
