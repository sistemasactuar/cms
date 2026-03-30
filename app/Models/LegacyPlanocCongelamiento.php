<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyPlanocCongelamiento extends Model
{
    protected $table = 'legacy_planoc_congelamientos';

    protected $fillable = [
        'legacy_source_table',
        'source_key',
        'legacy_id',
        'legacy_obligacion',
        'legacy_cedula_cliente',
        'legacy_nombre_cliente',
        'legacy_nombre_promotor',
        'legacy_sucursal',
        'legacy_saldo_actual',
        'legacy_dias_linix',
        'legacy_dias_actuar',
        'legacy_fecha_pago',
        'legacy_email',
        'legacy_whatsapp',
        'legacy_activo',
        'legacy_fecadi',
        'legacy_fecmod',
        'legacy_usuadi',
        'legacy_usumod',
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
