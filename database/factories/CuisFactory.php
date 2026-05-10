<?php

namespace Database\Factories;

use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cuis>
 */
class CuisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'valor' => fake()->unique()->bothify('?????#####'),
            'validez' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'estado' => 'ACTIVO',
            'sucursal_id' => null,
            'punto_venta_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
