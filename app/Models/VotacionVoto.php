<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VotacionVoto extends Model
{
    use HasFactory;

    protected $table = 'vot_votos';

    protected $fillable = [
        'votacion_id',
        'aportante_id',
        'planilla_id',
        'acepto_orden_dia_at',
        'voto_emitido_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'acepto_orden_dia_at' => 'datetime',
            'voto_emitido_at' => 'datetime',
        ];
    }

    public function votacion(): BelongsTo
    {
        return $this->belongsTo(Votacion::class, 'votacion_id');
    }

    public function aportante(): BelongsTo
    {
        return $this->belongsTo(Aportante::class, 'aportante_id');
    }

    public function planilla(): BelongsTo
    {
        return $this->belongsTo(VotacionPlanilla::class, 'planilla_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VotacionVotoDetalle::class, 'voto_id');
    }
}
