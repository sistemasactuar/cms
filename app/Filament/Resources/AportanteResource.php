<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AportanteResource\Pages;
use App\Models\Aportante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AportanteResource extends Resource
{
    protected static ?string $model = Aportante::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Votaciones';
    protected static ?string $navigationLabel = 'Aportantes';
    protected static ?string $modelLabel = 'Aportante';
    protected static ?string $pluralModelLabel = 'Aportantes';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del aportante')
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(180),
                        Forms\Components\TextInput::make('documento')
                            ->label('Documento')
                            ->required()
                            ->maxLength(40)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('correo')
                            ->label('Correo')
                            ->email()
                            ->maxLength(180),
                        Forms\Components\TextInput::make('telefono')
                            ->label('Telefono')
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('password')
                            ->label('Contrasena')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Si se deja vacia al crear, se asigna el mismo documento como clave inicial.'),
                        Forms\Components\Toggle::make('activo')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('documento')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('correo')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Telefono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ultimo_ingreso_at')
                    ->label('Ultimo ingreso')
                    ->since()
                    ->sortable()
                    ->placeholder('Sin ingreso'),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado'),
            ])
            ->actions([
                Tables\Actions\Action::make('resetPassword')
                    ->label('Reset clave')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Aportante $record): void {
                        $record->update([
                            'password' => $record->documento,
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAportantes::route('/'),
            'create' => Pages\CreateAportante::route('/create'),
            'edit' => Pages\EditAportante::route('/{record}/edit'),
        ];
    }
}
