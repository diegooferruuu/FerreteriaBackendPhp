<?php

namespace Database\Factories;

use App\Models\Inventario;
use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventarioMovimiento>
 */
class InventarioMovimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $movimiento = Movimiento::where('tipo', '!=', 'INICIAL')->inRandomOrder()->first();
        return [
            'ingresos' => $movimiento->tipo == 'INGRESO' ? fake()->numberBetween(50, 1000) : null,
            'egresos' => $movimiento->tipo == 'EGRESO' ? fake()->numberBetween(50, 1000) : null,
            'precio' => fake()->randomFloat(2, 9, 11),
            'identificador' => '1',
            'origen' => $movimiento->tipo == 'INGRESO' ? 'venta' : 'compra',
            'fecha' => fake()->dateTimeBetween('+1 days', '+1 months'),
            'observaciones' => fake()->word(),
            'movimiento_id' => $movimiento->id,
            'inventario_id' => Inventario::inRandomOrder()->first()->id,
        ];
    }
}
