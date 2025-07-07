<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Plano4111Resource\Pages;
use App\Models\Plano4111;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Http\UploadedFile;
use Filament\Notifications\Notification;
use App\Jobs\ImportarPlano4111Job;
use Livewire\TemporaryUploadedFile;

class Plano4111Resource extends Resource
{
    protected static ?string $model = Plano4111::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Plano 4111';
    protected static ?string $navigationGroup = 'Importaciones';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cedula')->sortable(),
                Tables\Columns\TextColumn::make('asociado'),
                Tables\Columns\TextColumn::make('modalidad'),
                Tables\Columns\TextColumn::make('calificacion'),
                Tables\Columns\TextColumn::make('obligacion'),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('saldo_capital'),
            ])
            ->actions([
                Action::make('Importar Excel')
                    ->form([
                        Forms\Components\FileUpload::make('archivo')
                            ->label('Archivo Excel')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->storeFiles(false),
                    ])
                    ->action(function (array $data): void {
                        /** @var UploadedFile|TemporaryUploadedFile $archivo */
                        $archivo = $data['archivo'];

                        ImportarPlano4111Job::dispatch($archivo);

                        Notification::make()
                            ->title('Importación iniciada')
                            ->body('El archivo se está procesando.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Importar desde Excel')
                    ->modalSubmitActionLabel('Importar')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-up-tray'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlano4111s::route('/'),
        ];
    }
}
