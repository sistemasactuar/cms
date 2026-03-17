<?php

namespace Tests\Feature;

use App\Models\Aportante;
use App\Models\Votacion;
use App\Models\VotacionPlanilla;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class VotacionProcessAreaTest extends TestCase
{
    /** @test */
    public function it_can_login()
    {
        $documento = 'LOG' . rand(1000, 9999);
        $password = '123456';

        $participante = Aportante::create([
            'nombre' => 'Login Test',
            'documento' => $documento,
            'password' => Hash::make($password),
            'activo' => true
        ]);

        $response = $this->post('/votaciones/ingresar', [
            'documento' => $documento,
            'password' => $password
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/votaciones/panel');
    }
}
