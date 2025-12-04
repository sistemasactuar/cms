<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class EvaluacionProveedor extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_proveedors';

    protected $fillable = [
        'user_id',
        'proveedor_id',
        'responsable_id',
        'fecha',
        // 11 preguntas
        'pregunta_1',
        'pregunta_2',
        'pregunta_3',
        'pregunta_4',
        'pregunta_5',
        'pregunta_6',
        'pregunta_7',
        'pregunta_8',
        'pregunta_9',
        'pregunta_10',
        'pregunta_11',
        'puntos_obtenidos',
        'puntos_posibles',
        'calificacion',
        'clasificacion',
        'observaciones',
        'firma',
        'bloqueado',
        'vobo_observaciones',
        'vobo_fecha',
        'vobo_user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'bloqueado' => 'boolean',
        'vobo_fecha' => 'datetime',
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(\App\Models\Proveedores::class, 'proveedor_id');
    }

    public function responsable()
    {
        return $this->belongsTo(\App\Models\Responsable::class, 'responsable_id');
    }

    public function voboUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'vobo_user_id');
    }

    // Cálculo automático de resultados y bloqueo tras firma
    protected static function booted()
    {
        static::saving(function ($model) {
            // Calcular puntuación solo si no está bloqueado
            $respuestas = collect(range(1, 11))
                ->map(fn($i) => $model->{"pregunta_$i"})
                ->filter(fn($v) => $v !== null && $v !== 'na');

            $model->puntos_obtenidos = $respuestas->sum();
            $model->puntos_posibles = $respuestas->count() * 2;
            $model->calificacion = $model->puntos_posibles > 0
                ? round(($model->puntos_obtenidos / $model->puntos_posibles) * 100, 2)
                : null;

            $model->clasificacion = match (true) {
                $model->calificacion >= 100 => 'EXCELENTE',
                $model->calificacion >= 90  => 'BUENO',
                $model->calificacion >= 80  => 'ACEPTABLE',
                default                     => 'NO ACEPTABLE',
            };

            // Si ya tiene firma, se bloquea
            if (!empty($model->firma)) {
                $model->bloqueado = true;
            }
        });
    }
}
