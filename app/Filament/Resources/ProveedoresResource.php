<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Proveedores;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\ProveedoresResource\Pages;

class ProveedoresResource extends Resource
{
    protected static ?string $model = Proveedores::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'Gestión de Proveedores';
    protected static ?string $modelLabel = 'Gestión de Proveedor';
    protected static ?string $pluralModelLabel = 'Gestiones de Proveedores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                    TextInput::make('contacto')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('nombre')
                          ->label('Nombre del proveedor')
                          ->required()
                          ->maxLength(255),

                    TextInput::make('servicio')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('responsable_id')
                        ->label('Responsable de Evaluación')
                        ->relationship('responsable', 'nombre')
                        ->searchable()
                        ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('contacto')->searchable(),
                Tables\Columns\TextColumn::make('servicio')->searchable(),
                Tables\Columns\TextColumn::make('responsable.nombre')
                ->label('Responsable')
                ->sortable()
                ->searchable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // ✅ ahora puedes eliminar uno por uno
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedores::route('/create'),
            'edit' => Pages\EditProveedores::route('/{record}/edit'),
        ];
    }
}
