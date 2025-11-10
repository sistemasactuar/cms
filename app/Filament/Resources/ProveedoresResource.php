<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedoresResource\Pages;
use App\Models\Proveedores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;

class ProveedoresResource extends Resource
{
    protected static ?string $model = Proveedores::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('contacto')
                    ->required()
                    ->maxLength(255),
                TextInput::make('servicio')
                    ->required()
                    ->maxLength(255),
                TextInput::make('responsable')
                    ->required()
                    ->maxLength(255),
                TextInput::make('telefono_resp')
                    ->label('Teléfono Responsable')
                    ->required()
                    ->tel()
                    ->regex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                TextInput::make('correo_resp')
                    ->label('Correo Responsable')
                    ->required()
                    ->email()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('contacto')->searchable(),
                Tables\Columns\TextColumn::make('servicio')->searchable(),
                Tables\Columns\TextColumn::make('responsable')->searchable(),
                Tables\Columns\TextColumn::make('telefono_resp')->searchable(),
                Tables\Columns\TextColumn::make('correo_resp')->searchable(),
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
