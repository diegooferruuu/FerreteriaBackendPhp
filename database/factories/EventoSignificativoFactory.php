<?php

namespace Database\Factories;

use App\Models\PuntoVenta;
use App\Models\ValorCatalogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventoSignificativo>
 */
class EventoSignificativoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'cufd_evento' => fake()->bothify('????###????'),
            'cafc' => null,
            'descripcion' => fake()->paragraph(1),
            'fecha_inicio' => now(),
            'fecha_fin' => null,
            'estado' => 'INICIADO',
            'codigo_recepcion' => null,
            'evento_id' => ValorCatalogo::whereIn('id', ['702', '703', '704', '705'])->value('id'),
            'sucursal_id' => null,
            'punto_venta_id' => PuntoVenta::inRandomOrder()->value('id'),
        ];
    }
}
