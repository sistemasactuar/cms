<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\OdinService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ConsultarCliente extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static string $view = 'filament.pages.consultar-cliente';

    protected static ?string $title = 'Consulta de Cliente';

    protected static ?string $navigationLabel = 'Consultar Cliente';

    protected static ?string $slug = 'consultar-cliente';

    public $identificacion = '';
    public $loading = false;
    public $searched = false;

    // Data properties
    public $cliente = null;
    public $infoBasica = null;
    public $empresa = null; // Maybe irrelevant if it's always the same context
    public $direccion = null;
    public $vinculacion = null;
    public $infoLaboral = null;
    public $estatutaria = null;
    public $obligaciones = []; // Using array for table data

    public function mount()
    {
        // Initialize if needed
    }

    public function buscar(OdinService $odinService)
    {
        $this->validate([
            'identificacion' => 'required|numeric|digits_between:5,20',
        ]);

        $this->loading = true;
        $this->searched = true;

        // Reset data
        $this->resetData();

        try {
            $clienteResp = $odinService->getClienteByIdentificacion($this->identificacion);
            $idCliente = $this->extractClientId($clienteResp);

            if (!$idCliente) {
                Notification::make()
                    ->title('Cliente no encontrado')
                    ->warning()
                    ->send();
                $this->loading = false;
                return;
            }

            $this->cliente = $this->extractFirstRow($clienteResp);
            $this->infoBasica = $this->extractFirstRow($odinService->getClienteInformacionBasica($idCliente));
            $this->direccion = $this->extractFirstRow($odinService->getClienteDireccion($idCliente));
            $this->vinculacion = $this->extractFirstRow($odinService->getClienteVinculacion($idCliente));
            $this->infoLaboral = $this->extractFirstRow($odinService->getClienteInfoLaboral($idCliente));
            $this->estatutaria = $this->extractFirstRow($odinService->getEstatutaria($idCliente));
            $this->obligaciones = $this->extractRows($odinService->getObligacion($idCliente));

            Notification::make()
                ->title('Información cargada exitosamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error buscando cliente: ' . $e->getMessage());
            Notification::make()
                ->title('Error consultando el servicio')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->loading = false;
        }
    }

    protected function resetData()
    {
        $this->cliente = null;
        $this->infoBasica = null;
        $this->direccion = null;
        $this->vinculacion = null;
        $this->infoLaboral = null;
        $this->estatutaria = null;
        $this->obligaciones = [];
    }

    protected function extractClientId(array $response): ?string
    {
        $row = $this->extractFirstRow($response);

        return $row['codigoCliente']
            ?? $row['CODIGO_CLIENTE']
            ?? $row['id']
            ?? null;
    }

    protected function extractFirstRow(array $response): ?array
    {
        if ($response === [] || ($response['_status'] ?? null) === 204) {
            return null;
        }

        if (isset($response['items']) && is_array($response['items'])) {
            return $response['items'][0] ?? null;
        }

        if (array_is_list($response)) {
            return $response[0] ?? null;
        }

        return $response;
    }

    protected function extractRows(array $response): array
    {
        if ($response === [] || ($response['_status'] ?? null) === 204) {
            return [];
        }

        if (isset($response['items']) && is_array($response['items'])) {
            return $response['items'];
        }

        if (array_is_list($response)) {
            return $response;
        }

        return [$response];
    }
}
