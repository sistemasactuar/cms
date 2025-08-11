<?php

namespace App\Filament\Imports;

use App\Models\Plano4111;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Plano4111Importer extends Importer
{
    protected static ?string $model = Plano4111::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('cedula'),
            ImportColumn::make('asociado'),
            ImportColumn::make('modalidad'),
            ImportColumn::make('calificacion'),
            ImportColumn::make('obligacion'),
            ImportColumn::make('telefono'),
            ImportColumn::make('celular'),
            ImportColumn::make('ciudad'),
            ImportColumn::make('saldo_capital'),
            ImportColumn::make('capital_vencido'),
            ImportColumn::make('dias_vencidos'),
            ImportColumn::make('asesor'),
        ];
    }

    public function resolveRecord(): ?Model
    {
        $row = $this->getRowData();

        if (Plano4111::where('obligacion', $row['obligacion'])->exists()) {
            return null;
        }

        return new Plano4111($row);
    }

    public function afterImport(Collection $rows): void
    {
        // Opcional: bitácora
    }

    public static function getCompletedNotificationBody(int $successfulRowsCount, int $failedRowsCount, Importer $importer): string
    {
        return "Importación completada: $successfulRowsCount registros exitosos, $failedRowsCount omitidos.";
    }
}
