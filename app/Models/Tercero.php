<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tercero extends Model
{
protected $fillable = [
        'nombre_tercero',
        'tipo_id',
        'digito_verificacion',
        'naturaleza',
        'sexo',
        'estado_civil',
        'fecha_nacimiento',
        'nivel_educativo',
        'numero_hijos',
        'numero_dependientes',
        'direccion',
        'barrio',
        'telefono_fijo',
        'celular',
        'correo',
        'tipo_asociado',
        'profesion',
        'fecha_ingreso_empresa',
        'sueldo_basico',
        'otros_ingresos_mes',
        'tipo_contrato',
        'pais_nacimiento',
        'departamento_nacimiento',
        'ciudad_nacimiento',
        'estado_asociado',
        'fecha_ultima_actualizacion',
        'aut_notifi',
        'aut_cons_cen_ries',
    ];
}
