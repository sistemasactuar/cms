<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MacroTipoActivo extends Model
{
    protected $table = 'para_macro_tipo_activo';

    protected $fillable = [
        'nombre',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tipos(): HasMany
    {
        return $this->hasMany(TipoActivo::class, 'macro_tipo_id');
    }
}

