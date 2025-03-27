<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Card;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationGroup = 'Sistemas';
    protected static ?string $navigationLabel = 'Aplicaciones';

    public static function getModelLabel(): string
    {
        return 'Aplicacion'; // Singular
    }

    public static function getPluralModelLabel(): string
    {
        return 'Aplicaciones'; // Plural
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make('Inventario de aplicaciones')->schema([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->unique()
                    ->maxLength(255),
                
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),

                TextInput::make('version')
                    ->label('Versión')
                    ->maxLength(50),

                TextInput::make('url')
                    ->label('Link'),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
                        'Mantenimiento' => 'Mantenimiento',
                    ])
                    ->default('Activo')
                    ->required(),
                    
                    Forms\Components\ToggleButtons::make('publico')
                    ->label('¿Es pública?')
                    ->boolean()
                    ->default(true)
                    ->inline(),

                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('name')->label('Nombre')->sortable()->searchable(),
            TextColumn::make('description')->label('Descripción')->limit(50)->wrap(),
            TextColumn::make('version')->label('Versión')->sortable(),
            BadgeColumn::make('status')
                ->label('Estado')
                ->colors([
                    'Activo' => 'success',
                    'Inactivo' => 'danger',
                    'Mantenimiento' => 'warning',
                ])
                ->sortable(),
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
