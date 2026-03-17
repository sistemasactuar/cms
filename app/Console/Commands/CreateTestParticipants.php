<?php

namespace App\Console\Commands;

use App\Models\Aportante;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestParticipants extends Command
{
    protected $signature = 'votacion:create-test-participants {count=10} {--pass=123456}';
    protected $description = 'Crea participantes de prueba para el sistema de votaciones';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $password = $this->option('pass');

        // 1. Crear participantes
        $this->info("Creando {$count} participantes con la clave: {$password}");
        for ($i = 1; $i <= $count; $i++) {
            $doc = "TEST" . str_pad($i, 4, '0', STR_PAD_LEFT);
            Aportante::updateOrCreate(
                ['documento' => $doc],
                [
                    'nombre' => "Participante de Prueba {$i}",
                    'correo' => "test{$i}@example.com",
                    'password' => Hash::make($password),
                    'activo' => true
                ]
            );
        }

        // 2. Asegurar datos en la primera votacion disponible
        $votacion = \App\Models\Votacion::first();
        if ($votacion) {
            $this->info("Configurando datos de prueba para: {$votacion->titulo}");

            if ($votacion->tipo_votacion === 'planilla' && $votacion->planillas()->count() === 0) {
                foreach (range(1, 3) as $n) {
                    $plancha = \App\Models\VotacionPlanilla::create([
                        'votacion_id' => $votacion->id,
                        'numero' => $n,
                        'nombre' => "Plancha Test {$n}",
                        'activo' => true,
                        'color' => '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
                    ]);

                    foreach (range(1, 3) as $cn) {
                        \App\Models\VotacionCandidato::create([
                            'votacion_id' => $votacion->id,
                            'planilla_id' => $plancha->id,
                            'nombre' => "Integrante {$cn} de Plancha {$n}",
                            'documento' => '123' . $n . $cn,
                            'activo' => true,
                        ]);
                    }
                }
                $this->line("Creadas 3 planchas con 3 integrantes cada una.");
            }
        }

        $this->info("¡Proceso terminado! Ya puedes loguearte con estos documentos en local.");
    }
}
