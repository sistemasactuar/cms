<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoFijoResponsableHistorial extends Model
{
    protected $table = 'proc_activofijo_responsable_historial';

    protected $fillable = [
        'activo_fijo_id',
        'usuario_anterior_id',
        'usuario_nuevo_id',
        'responsable_anterior',
        'responsable_nuevo',
        'motivo',
        'changed_by_user_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(ActivoFijo::class, 'activo_fijo_id');
    }

    public function usuarioAnterior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_anterior_id');
    }

    public function usuarioNuevo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_nuevo_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}

