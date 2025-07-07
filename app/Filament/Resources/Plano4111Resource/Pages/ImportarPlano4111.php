<?php

namespace App\Filament\Resources\Plano4111Resource\Pages;

use App\Filament\Resources\Plano4111Resource;
use App\Imports\Plano4111Import;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;

class ImportarPlano4111 extends Page
{
    use Forms\Concerns\InteractsWithForms;
    use WithFileUploads;

    public mixed $archivo = null;

    protected static string $resource = Plano4111Resource::class;

    protected static string $view = 'filament.resources.plano4111-resource.pages.importar-plano4111';

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
         FileUpload::make('archivo')
            ->label('Archivo Excel')
            ->required()
            ->storeFiles(false) // No subir automáticamente
            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
        ];
    }

    public function importar()
    {
        request()->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = request()->file('archivo');
        $path = $file->store('importaciones-temporales');

        Excel::import(new Plano4111Import, $path);

        Notification::make()
            ->title('Importación completada correctamente.')
            ->success()
            ->send();

        return redirect(Plano4111Resource::getUrl('index'));
    }

    public function importarArchivo()
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        // Asegurar que sea instancia de UploadedFile
        if (is_array($this->archivo)) {
            $this->archivo = $this->archivo[0]; // Forzar a uno solo si vino como array
        }

        $path = $this->archivo->store('importaciones-temporales');

        Excel::import(new Plano4111Import, $path);

        Notification::make()
            ->title('Importación exitosa')
            ->success()
            ->send();

        $this->redirect(Plano4111Resource::getUrl('index'));
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('importar')
                ->label('Importar')
                ->submit('importarArchivo'),
        ];
    }
}
