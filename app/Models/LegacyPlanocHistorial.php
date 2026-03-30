<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyPlanocHistorial extends Model
{
    protected $table = 'legacy_planoc_historiales';

    protected $fillable = [
        'tipo_historial',
        'legacy_source_table',
        'source_key',
        'legacy_id',
        'legacy_obligacion',
        'legacy_cedula_cliente',
        'legacy_periodo',
        'legacy_activo',
        'legacy_fecadi',
        'legacy_usuadi',
        'payload',
        'legacy_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'legacy_activo' => 'boolean',
            'legacy_synced_at' => 'datetime',
        ];
    }
}
