<?php

namespace Database\Factories;

use App\Models\ClasificacionProducto;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\PrecioGeneral;
use App\Models\Proveedor;
use App\Models\SubLinea;
use App\Models\TipoProducto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->unique()->bothify('?????#####'),
            'codigo_alternativo' => fake()->unique()->bothify('p-?????-#####'),
            'producto' => fake()->word(),
            'descripcion' => fake()->word(),
            'imagen' => fake()->image(null, 360, 360),
            'codigo_barra' => fake()->ean8(),
            'codigo_qr' => fake()->ean13(),
            // 'costo_promedio' => fake()->randomFloat(2, 10, 100),
            'estado' => 'ACTIVO',
            'linea_id' => Linea::inRandomOrder()->first()->id_linea,
            'sub_linea_id' => SubLinea::inRandomOrder()->first()->id_sub_linea,
            'grupo_id' => Grupo::inRandomOrder()->first()->id_grupo,
            'tipo_producto_id' => TipoProducto::inRandomOrder()->first()->id_tipo_producto,
            'proveedor_id' => Proveedor::inRandomOrder()->first()->id_proveedor,
            'clasificacion_producto_id' => ClasificacionProducto::inRandomOrder()->first()->id,
//            'precio_general_id' => PrecioGeneral::inRandomOrder()->first()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
