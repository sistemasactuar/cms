<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plano_mora extends Model
{
    protected $table = 'plano_moras';

    protected $fillable = [
    'cedula_cliente',
    'nombre_cliente',
    'sucursal',
    'cedula_prom',
    'nombre_prom',
    'obligacion',
    'calificacion',
    'monto',
    'saldo_actual',
    'vencido_linix',
    'vencido_menor_77',
    'vencido_actuar',
    'dias_linix',
    'dias_actuar',
    'saldo_total',
    'direccion',
    'barrio',
    'telefono',
    'ciudad',
    'cedula_cod1',
    'nombre_cod1',
    'tel_cod1',
    'direccion_cod1',
    'cedula_cod2',
    'nombre_cod2',
    'tel_cod2',
    'direccion_cod2',
    'cedula_cod3',
    'nombre_cod3',
    'tel_cod3',
    'direccion_cod3',
    ];

}
