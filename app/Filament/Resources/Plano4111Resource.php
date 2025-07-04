<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Plano4111Resource\Pages;
use App\Filament\Resources\Plano4111Resource\RelationManagers;
use App\Models\Plano4111;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Plano4111Resource extends Resource
{
    protected static ?string $model = Plano4111::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Plano 4111';
    protected static ?string $navigationGroup = 'ERP';
    protected static ?int $navigationSort = 50;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula')->searchable(),
                Tables\Columns\TextColumn::make('asociado')->searchable(),
                Tables\Columns\TextColumn::make('modalidad')->searchable(),
                Tables\Columns\TextColumn::make('calificacion')->searchable(),
                Tables\Columns\TextColumn::make('obligacion')->searchable(),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('celular'),
                Tables\Columns\TextColumn::make('ciudad'),
                Tables\Columns\TextColumn::make('saldo_capital')->money('COP', true)->sortable(),
                Tables\Columns\TextColumn::make('capital_vencido')->money('COP', true)->sortable(),
                Tables\Columns\TextColumn::make('dias_vencidos')->sortable(),
                Tables\Columns\TextColumn::make('asesor')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Importado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ciudad')->options(
                    fn () => Plano4111::query()->distinct()->pluck('ciudad', 'ciudad')->filter()->toArray()
                ),
                Tables\Filters\SelectFilter::make('modalidad')->options(
                    fn () => Plano4111::query()->distinct()->pluck('modalidad', 'modalidad')->filter()->toArray()
                ),
                Tables\Filters\SelectFilter::make('calificacion')->options(
                    fn () => Plano4111::query()->distinct()->pluck('calificacion', 'calificacion')->filter()->toArray()
                ),
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
            'index' => Pages\ListPlano4111s::route('/'),
            'create' => Pages\CreatePlano4111::route('/create'),
            'edit' => Pages\EditPlano4111::route('/{record}/edit'),
        ];
    }
}
