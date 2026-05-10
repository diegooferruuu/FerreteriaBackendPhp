<?php

namespace Database\Factories;

use App\Models\SincronizacionCatalogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ValorCatalogo>
 */
class ValorCatalogoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'codigo_clasificador' => fake()->unique()->bothify('?????#####'),
            'codigo_actividad' => fake()->unique()->bothify('?????#####'),
            'descripcion' => fake()->name(),
            'estado' => 'ACTIVO',
            'sincronizacion_catalogo_id' => SincronizacionCatalogo::inRandomOrder()->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
