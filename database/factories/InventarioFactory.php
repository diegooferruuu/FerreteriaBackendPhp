<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\Precio;
use App\Models\PrecioParticular;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario>
 */
class InventarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'cantidad' => fake()->numberBetween(1, 100),
            'cantidad_maxima' => fake()->numberBetween(50, 100),
            'cantidad_minima' => fake()->numberBetween(1, 10),
            'producto_id' => Producto::inRandomOrder()->first()->id,
            'sucursal_id' => Sucursal::inRandomOrder()->first()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
