<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $fillable = [
        'nombre',
        'valor',
        'tipo',
        'activo',
    ];
}
