<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\ValorCatalogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'razon_social' => fake()->name(),
            'cedula_nit' => fake()->randomNumber(8, true),
            'complemento' => fake()->lexify('?'),
            'telefono' => fake()->randomNumber(6, true),
            'email' => fake()->email(),
            'direccion' => fake()->address(),
            'estado' => 'ACTIVO',
            'departamento_id' => Departamento::inRandomOrder()->first()->id,
            'tipo_documento_id' => ValorCatalogo::where('sincronizacion_catalogo_id', 6)->inRandomOrder()->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
