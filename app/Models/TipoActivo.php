<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoActivo extends Model
{
    protected $table = 'para_tipo_activo';

    protected $fillable = [
        'macro_tipo_id',
        'tipo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function macroTipo(): BelongsTo
    {
        return $this->belongsTo(MacroTipoActivo::class, 'macro_tipo_id');
    }

    public function activos(): HasMany
    {
        return $this->hasMany(ActivoFijo::class, 'tipo');
    }
}
