<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividades extends Model
{
    protected $table = 'actividades';

    protected $fillable = [
        'titulo', 'descripcion', 'fecha_programada', 'estado',
        'latitud', 'longitud', 'user_id',
    ];

}
