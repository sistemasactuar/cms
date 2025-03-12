<?php

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditoriaObserver
{
    /**
     * Handle the "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction($model, 'created');
    }

    /**
     * Handle the "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logAction($model, 'updated');
    }

    /**
     * Handle the "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction($model, 'deleted');
    }

    /**
     * Registrar la acción en la tabla de auditoría.
     */
    private function logAction(Model $model, string $action): void
    {
        $user = auth()->user();

        Auditoria::create([
            'user_id' => $user ? $user->id : null,
            'accion' => $action,
            'modelo' => get_class($model),
            'modelo_id' => $model->id,
            'cambios' => $action === 'updated' ? $model->getChanges() : [],
            'ip' => Request::ip(),
            'navegador' => Request::header('User-Agent'),
        ]);
    }
}
