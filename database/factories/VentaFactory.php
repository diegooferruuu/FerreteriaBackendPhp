<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\MetodoPago;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoVenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $pos = PuntoVenta::with('sucursal')->inRandomOrder()->first();
        return [
            'codigo_secuencia' => fake()->numerify(),
            'total' => fake()->numberBetween(0, 100),
            'descuento' => 0,
            'fecha' => now(),
            'descripcion' => null,
            'informacion_tarjeta' => null,
            'estado' => 'ACTIVO',
            'metodo_pago_id' => MetodoPago::inRandomOrder()->value('id'),
            'sucursal_id' => $pos->sucursal->id,
            'cliente_id' => Cliente::inRandomOrder()->value('id'),
            'tipo_venta_id' => TipoVenta::inRandomOrder()->value('id_tipo_venta'),
            'punto_venta_id' => $pos->id,
        ];
    }
}
