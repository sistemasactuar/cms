<?php

namespace Database\Factories;

use App\Models\Aportante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AportanteFactory extends Factory
{
    protected $model = Aportante::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'documento' => $this->faker->unique()->numberBetween(10000000, 999999999),
            'correo' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'password' => Hash::make('password'),
            'activo' => true,
        ];
    }
}
