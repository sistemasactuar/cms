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
        'responsable_user_id',
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
        'responsable_user_id' => 'integer',
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

        static::created(function (self $model): void {
            self::registrarCambioResponsable($model, true);
        });

        static::updated(function (self $model): void {
            if (!$model->wasChanged(['responsable', 'responsable_user_id'])) {
                return;
            }

            self::registrarCambioResponsable($model, false);
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

    public function responsableUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_user_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(ActivoMantenimiento::class, 'equipo_id');
    }

    public function historialResponsables(): HasMany
    {
        return $this->hasMany(ActivoFijoResponsableHistorial::class, 'activo_fijo_id')
            ->orderByDesc('changed_at')
            ->orderByDesc('id');
    }

    private static function registrarCambioResponsable(self $model, bool $isCreate): void
    {
        $usuarioAnteriorId = $isCreate ? null : $model->getOriginal('responsable_user_id');
        $usuarioNuevoId = $model->responsable_user_id;

        $responsableAnterior = $isCreate ? null : trim((string) $model->getOriginal('responsable'));
        $responsableNuevo = trim((string) $model->responsable);

        if (
            (int) ($usuarioAnteriorId ?? 0) === (int) ($usuarioNuevoId ?? 0) &&
            $responsableAnterior === $responsableNuevo
        ) {
            return;
        }

        if (
            $usuarioAnteriorId === null &&
            $usuarioNuevoId === null &&
            $responsableAnterior === '' &&
            $responsableNuevo === ''
        ) {
            return;
        }

        $model->historialResponsables()->create([
            'usuario_anterior_id' => $usuarioAnteriorId,
            'usuario_nuevo_id' => $usuarioNuevoId,
            'responsable_anterior' => $responsableAnterior !== '' ? $responsableAnterior : null,
            'responsable_nuevo' => $responsableNuevo !== '' ? $responsableNuevo : null,
            'changed_by_user_id' => auth()->id(),
            'changed_at' => now(),
        ]);
    }
}
