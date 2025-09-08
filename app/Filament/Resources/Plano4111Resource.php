<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Plano4111Resource\Pages;
use App\Filament\Imports\Plano4111Importer;
use App\Models\Plano4111;
use Filament\Resources\Resource;
use Filament\Tables;
use HayderHatem\FilamentExcelImport\Actions\FullImportAction;
use HayderHatem\FilamentExcelImport\Actions\Concerns\CanImportExcelRecords;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;



class Plano4111Resource extends Resource
{
    use CanImportExcelRecords;

    protected static ?string $model = Plano4111::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Plano 4111';
    protected static ?string $navigationGroup = 'Importaciones';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula'),
                Tables\Columns\TextColumn::make('asociado'),
                Tables\Columns\TextColumn::make('modalidad'),
                Tables\Columns\TextColumn::make('calificacion'),
                Tables\Columns\TextColumn::make('obligacion'),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('saldo_capital'),
            ]);
    }

    public static function getHeaderActions(): array
    {
        return [
        FullImportAction::make()
            ->importer(Plano4111Importer::class)
            ->label('Importar Plano 4111')
            ->icon('heroicon-o-document-upload')
            ->color('success')
            ->modalHeading('Importar Plano 4111')
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlano4111s::route('/'),
        ];
    }
}
