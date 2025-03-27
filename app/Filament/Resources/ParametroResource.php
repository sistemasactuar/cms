<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParametroResource\Pages;
use App\Filament\Resources\ParametroResource\RelationManagers;
use App\Models\Parametro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParametroResource extends Resource
{
    protected static ?string $model = Parametro::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $label = 'Parámetro';
    protected static ?string $pluralLabel = 'Parámetros';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'texto' => 'Texto',
                        'booleano' => 'Booleano',
                        'entero' => 'Entero',
                        'decimal' => 'Decimal',
                        'json' => 'JSON',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\Textarea::make('valor')
                    ->label('Valor')
                    ->rows(3)
                    ->required(),

                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('nombre')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('tipo')->label('Tipo')->badge(),
            Tables\Columns\IconColumn::make('activo')->boolean(),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')->options([
                    'texto' => 'Texto',
                    'booleano' => 'Booleano',
                    'entero' => 'Entero',
                    'decimal' => 'Decimal',
                    'json' => 'JSON',
                ]),
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
            'index' => Pages\ListParametros::route('/'),
            'create' => Pages\CreateParametro::route('/create'),
            'edit' => Pages\EditParametro::route('/{record}/edit'),
        ];
    }
}
