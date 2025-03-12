<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $fillable = [
        'user_id',
        'accion',
        'modelo',
        'modelo_id',
        'cambios',
        'ip',
        'navegador',
    ];

    protected $casts = [
        'cambios' => 'array',
    ];
}
