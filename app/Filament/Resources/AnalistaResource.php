<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Analistas;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AnalistaResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AnalistaResource\RelationManagers;
use App\Filament\Resources\AnalistaResource\Pages\ListAnalistas;

class AnalistaResource extends Resource
{
    protected static ?string $model = Analistas::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Parametros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Información Analista')->schema([
                    Forms\Components\TextInput::make('Cedula_Prom')
                        ->label('Cédula Promotor')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('AP_Nombre1')
                        ->label('Primer Nombre')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('AP_Nombre2')
                        ->label('Segundo Nombre')
                        ->maxLength(50),
                    Select::make('sede_id')
                        ->label('Sede')
                        ->relationship('sede', 'nombresede')
                        ->searchable()
                        ->required(),
                    ])->columns(2),
            ])->columns(2);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('AP_Nombre1')->label('Primer Nombre'),
                TextColumn::make('AP_Nombre2')->label('Segundo Nombre'),
                TextColumn::make('sede.nombresede')->label('Sede')

            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalistas::route('/'),
            'create' => Pages\CreateAnalista::route('/create'),
            'edit' => Pages\EditAnalista::route('/{record}/edit'),
        ];
    }
}
