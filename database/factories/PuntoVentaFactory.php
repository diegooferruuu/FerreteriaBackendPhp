<?php

namespace Database\Factories;

use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PuntoVenta>
 */
class PuntoVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'codigo_siat' => fake()->unique()->randomNumber(3, false),
            'nombre' => fake()->name(),
            'descripcion' => fake()->paragraph(1),
            'estado' => 'ACTIVO',
            'tipo_punto_venta_id' => ValorCatalogo::where('sincronizacion_catalogo_id', 13)->inRandomOrder()->value('id'),
            'sucursal_id' => Sucursal::inRandomOrder()->value('id'),
        ];
    }
}
