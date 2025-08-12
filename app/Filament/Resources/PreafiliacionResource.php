<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreafiliacionResource\Pages;
use App\Models\Preafiliacion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PreafiliacionResource extends Resource
{
    protected static ?string $model = Preafiliacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Trámites';
    protected static ?string $navigationLabel = 'Preafiliaciones';

    public static function getModelLabel(): string
    {
        return 'Preafiliación';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Preafiliaciones';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                TextInput::make('monto_solicitado')->label('Monto solicitado')->numeric(),
                TextInput::make('cuota_propuesta')->label('Cuota propuesta')->numeric(),
                TextInput::make('destino')->label('Destino del crédito'),

                TextInput::make('nombre')->label('Nombre')->required(),
                TextInput::make('cedula')->label('Cédula de ciudadanía')->required(),
                TextInput::make('direccion')->label('Dirección')->required(),
                TextInput::make('ciudad')->label('Ciudad')->required(),
                TextInput::make('telefonos')->label('Teléfonos'),
                TextInput::make('email')->label('Correo electrónico')->email(),

                Select::make('vivienda')
                    ->label('Tipo de vivienda')
                    ->options([
                        'Propia' => 'Propia',
                        'Arrendada' => 'Arrendada',
                        'Familiar' => 'Familiar',
                    ]),

                TextInput::make('actividad')->label('Actividad económica'),
                TextInput::make('antiguedad')->label('Antigüedad del negocio'),

                Toggle::make('autorizado')
                    ->label('Autorización de tratamiento de datos')
                    ->inline()
                    ->required(),
            ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nombre')->label('Nombre')->sortable()->searchable(),
            TextColumn::make('cedula')->label('Cédula')->sortable()->searchable(),
            TextColumn::make('ciudad')->label('Ciudad'),
            TextColumn::make('monto_solicitado')->label('Monto')->money('COP'),
            Tables\Columns\BooleanColumn::make('autorizado')
                ->label('Autorizado')
                ->boolean()
                ->colors([
                    'danger' => false,
                    'success' => true,
                ]),
            TextColumn::make('created_at')->label('Fecha')->dateTime(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPreafiliacions::route('/'),
            'create' => Pages\CreatePreafiliacion::route('/create'),
            'edit' => Pages\EditPreafiliacion::route('/{record}/edit'),
        ];
    }
}
