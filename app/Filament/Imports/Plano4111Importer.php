<?php

namespace App\Filament\Imports;

use App\Models\Plano4111;
use Illuminate\Support\Collection;
use Filament\Actions\Imports\Importer;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Imports\ImportColumn;


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


            if (\App\Models\Plano4111::where('obligacion', $row['obligacion'])->exists()) {
                return null;
            }

            $plano = new \App\Models\Plano4111($row);

            // Puedes usar $departmentId o $sendEmail si aplica
            // $plano->department_id = $departmentId;

            return $plano;
        }

    public function afterImport(Collection $rows): void
    {
        // Puedes dejarlo vacío o registrar una bitácora
    }

    public static function getCompletedNotificationBody($import): string
    {
        $total = $import->getTotalRowsCount();
        return "La importación se completó exitosamente. Total de registros importados: {$total}.";
    }
}
