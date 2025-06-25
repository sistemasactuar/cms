<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Plano_cartera;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class InformeCartera extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $desde = null;
    public ?string $hasta = null;

    public ?string $graficoMontoModalidad = null;
    public ?string $graficoSaldoClasificacion = null;
    public ?string $graficoSaldoRiesgo = null;

    public array $graficas = [];

    protected static string $view = 'filament.pages.informe-cartera';
    protected static ?string $title = 'Informe de Cartera';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('desde')->label('Desde')->required(),
            DatePicker::make('hasta')->label('Hasta')->required(),
        ];
    }

    public function generar(): void
    {
        $query = Plano_cartera::query();

        if ($this->desde && $this->hasta) {
            $query->whereBetween('fec_liquidacion', [$this->desde, $this->hasta]);
        }

        $datos = $query->get();

        $this->graficas = [
            'modalidad' => $datos->groupBy('modalidad')->map->count()->toArray(),
            'clasificacion' => $datos->groupBy('clasificacion')->map->count()->toArray(),
            'riesgo_valor' => $datos->groupBy('calif_aplicada')->map(fn ($g) => $g->sum('saldo_capital'))->toArray(),
        ];

        $this->graficoMontoModalidad = $this->generarGrafico(
            'Obligaciones por Modalidad',
            array_keys($this->graficas['modalidad']),
            array_values($this->graficas['modalidad'])
        );

        $this->graficoSaldoClasificacion = $this->generarGrafico(
            'Clasificación',
            array_keys($this->graficas['clasificacion']),
            array_values($this->graficas['clasificacion'])
        );

        $this->graficoSaldoRiesgo = $this->generarGrafico(
            'Saldo por Nivel de Riesgo',
            array_keys($this->graficas['riesgo_valor']),
            array_values($this->graficas['riesgo_valor'])
        );
    }

    public function exportar(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $data = Plano_cartera::whereBetween('fec_liquidacion', [$this->desde, $this->hasta])->get();

        return Excel::download(new \App\Exports\PlanoCarteraExport($data), 'informe_cartera.xlsx');
    }

    private function generarGrafico(string $titulo, array $labels, array $data): string|null
    {
        $chartData = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $titulo,
                    'data' => $data,
                    'backgroundColor' => '#3b82f6',
                ]],
            ],
        ];

        $url = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartData));

        try {
            $image = file_get_contents($url);
            return base64_encode($image);
        } catch (\Exception $e) {
            return null;
        }
    }
}
