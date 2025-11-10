<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedores extends Model
{
   protected $fillable = [
        'nombre', 'contacto', 'servicio', 'responsable',
        'telefono_resp', 'correo_resp'
    ];
}
