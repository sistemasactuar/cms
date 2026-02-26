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
            // 1. Get Client basic lookup
            $clienteResp = $odinService->getClienteByIdentificacion($this->identificacion);

            // Check if we got a valid response structure. 
            // NOTE: Adapting logic to whatever the real API returns. 
            // Assuming $clienteResp contains an 'items' array or is the item itself.
            // For this implementation, I'll store the raw response to debug in view if needed, 
            // but ideally we need the 'id' (internal ID) for subsequent calls.

            // Heuristic to find ID:
            $idCliente = $clienteResp['id'] ?? $clienteResp['items'][0]['id'] ?? null;

            if (!$idCliente) {
                Notification::make()
                    ->title('Cliente no encontrado')
                    ->warning()
                    ->send();
                $this->loading = false;
                return;
            }

            $this->cliente = $clienteResp;

            // 2. Fetch all other details in parallel or sequence
            // Using sequence for simplicity and error isolation

            $this->infoBasica = $odinService->getClienteInformacionBasica($idCliente);
            $this->direccion = $odinService->getClienteDireccion($idCliente);
            $this->vinculacion = $odinService->getClienteVinculacion($idCliente);
            $this->infoLaboral = $odinService->getClienteInfoLaboral($idCliente);
            $this->estatutaria = $odinService->getEstatutaria($idCliente);

            // 3. Fetch Obligations
            $obligacionResp = $odinService->getObligacion($idCliente);
            // Assuming obligations might be a list
            $this->obligaciones = $obligacionResp['items'] ?? [$obligacionResp] ?? [];

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
}
