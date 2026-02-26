<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoMantenimiento extends Model
{
    protected $table = 'proc_mante_activofijo';

    protected $fillable = [
        'equipo_id',
        'tipo_M',
        'observacion_M',
        'usuadi',
        'fecadi',
        'horadi',
        'usumod',
        'fecmod',
        'hormod',
        'activo',
    ];

    protected $casts = [
        'tipo_M' => 'integer',
        'fecadi' => 'date',
        'fecmod' => 'date',
        'activo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->usuadi = $model->usuadi ?? auth()->id();
            $model->fecadi = $model->fecadi ?? now()->toDateString();
            $model->horadi = $model->horadi ?? now()->format('H:i:s');
            $model->activo = $model->activo ?? true;
        });

        static::updating(function (self $model): void {
            $model->usumod = auth()->id();
            $model->fecmod = now()->toDateString();
            $model->hormod = now()->format('H:i:s');
        });
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(ActivoFijo::class, 'equipo_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuadi');
    }

    public function modificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usumod');
    }
}
