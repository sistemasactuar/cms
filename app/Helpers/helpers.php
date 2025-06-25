<?php

if (!function_exists('parseFecha')) {
    function parseFecha(?string $fecha): ?string {
        $fecha = trim($fecha ?? '');

        if (!$fecha || $fecha === '0000-00-00') {
            return null;
        }

        // mm/dd/yyyy
        $dt = \DateTime::createFromFormat('m/d/Y', $fecha);
        return $dt ? $dt->format('Y-m-d') : null;
    }
}
