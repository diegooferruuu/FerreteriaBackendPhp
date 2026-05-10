<?php

namespace Database\Factories;

use App\Models\Cufd;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Factura>
 */
class FacturaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'venta_id' => Venta::factory(),
            'cufd_id' => Cufd::factory(),
            'xml' => '/path/file.xml',
            'codigo_documento_sector' => 1,
            'codigo_tipo_factura' => 1,
            'numero_documento_identidad' => fake()->randomNumber(7, true),
            'codigo_documento_identidad' => 1,
            'codigo_metodo_pago' => 1,
            'codigo_cliente' => fake()->randomNumber(5),
            'razon_social' => fake()->word(),
            'leyenda' => fake()->paragraph(1),
            'usuario' => fake()->lexify(),
            'cuf' => fake()->bothify('????###????##'),
            'cafc' => null,
            'estado' => 'PENDIENTE',
        ];
    }
}
