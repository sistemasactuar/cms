<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaMora extends Model
{
        protected $fillable = [
        'obligacion',
        'sucursal_anterior',
        'cedula_prom_anterior',
        'nombre_prom_anterior',
        'calificacion_anterior',
        'monto_anterior',
        'saldo_actual_anterior',
        'vencido_linix_anterior',
        'vencido_menor_77_anterior',
        'vencido_actuar_anterior',
        'dias_linix_anterior',
        'dias_actuar_anterior',
        'saldo_total_anterior',
        'fecha_modificacion',
        'user_id',
    ];

}
