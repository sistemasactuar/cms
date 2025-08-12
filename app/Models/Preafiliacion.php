<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preafiliacion extends Model
{
    protected $fillable = [
        'monto_solicitado',
        'cuota_propuesta',
        'destino',

        // Datos personales
        'nombre',
        'cedula',
        'direccion',
        'ciudad',
        'telefonos',
        'email',
        'vivienda', // valores: 'Propia', 'Arrendada', 'Familiar'

        // Datos del negocio
        'actividad',
        'antiguedad',

        // Autorización legal
        'autorizacion_datos', // boolean: true si acepta la autorización
        'autorizacion_centrales', // boolean: true si autoriza consulta en centrales
    ];
}
