<?php

namespace App\Enums;

enum EstadoActividad: string
{
    case EnCurso   = 'en_curso';
    case Ejecutada = 'ejecutada';
    case Finalizada= 'finalizada';

    public static function labels(): array
    {
        return [
            self::EnCurso->value    => 'En curso',
            self::Ejecutada->value  => 'Ejecutada',
            self::Finalizada->value => 'Finalizada',
        ];
    }
}
