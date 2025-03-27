<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SedeResource\Pages;
use App\Models\Sede;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SedeResource extends Resource
{
    protected static ?string $model = Sede::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Configuración';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Sede::class);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Configuración')->schema([
                    TextInput::make('NombreSede')
                        ->label('Nombre de la Sede')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Toggle::make('activo')
                        ->label('Activo')
                        ->default(true)
                        ->inline(false)
                        ->required(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('NombreSede')
                    ->label('Nombre de la Sede')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BooleanColumn::make('activo')
                    ->label('Activo'),
            ])
            ->filters([
                Tables\Filters\Filter::make('Activo')
                    ->query(fn ($query) => $query->where('activo', 1)),

                Tables\Filters\Filter::make('Inactivo')
                    ->query(fn ($query) => $query->where('activo', 0)),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function () {
                        $user = auth()->user();

                        // Si es administrador, siempre tiene permiso
                        if ($user->hasRole(['admin', 'Superadmin'])) {
                            return true;
                        }

                        // Verifica si el usuario tiene permiso explícito
                        return $user->can('Editar Sede');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null);
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
            'index' => Pages\ListSedes::route('/'),
            'create' => Pages\CreateSede::route('/create'),
            'edit' => Pages\EditSede::route('/{record}/edit'),
        ];
    }
}
