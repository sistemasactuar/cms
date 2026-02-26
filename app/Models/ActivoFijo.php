<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivoFijo extends Model
{
    protected $table = 'proc_activofijo';

    protected $fillable = [
        'tipo',
        'descripcion',
        'marca',
        'modelo',
        'serie',
        'codigo',
        'para_sede_id',
        'responsable',
        'valor',
        'condicion',
        'observacion',
        'unidad_cd',
        'hdd1',
        'tipo_disco',
        'hdd2',
        'tipo_disco2',
        'fuente',
        'cargador',
        'procesador',
        'ram',
        'pantalla',
        'pantalla_tam',
        't_video',
        'teclado',
        'mouse',
        'so',
        'sof',
        'compresor',
        'adobe',
        'antivirus',
        'explorador1',
        'explorador2',
        'explorador3',
        'prog_adicionales',
        'ups_capacidad',
        'telecom_puertos',
        'telecom_pe',
        'vigil_tipo',
        'vigil_puertos',
        'vigil_capacidad',
        'vigil_poe',
        'acces_point_rango',
        'por',
        'visto',
        'usuadi',
        'fecadi',
        'horadi',
        'usumod',
        'fecmod',
        'hormod',
        'activo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
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

    public function tipoActivo(): BelongsTo
    {
        return $this->belongsTo(TipoActivo::class, 'tipo');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'para_sede_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(ActivoMantenimiento::class, 'equipo_id');
    }
}
