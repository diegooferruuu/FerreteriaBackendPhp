<?php

namespace Database\Factories;

use App\Models\Cuis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cufd>
 */
class CufdFactory extends Factory
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
            'codigo_control' => fake()->unique()->bothify('?????#####'),
            'validez' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'estado' => 'ACTIVO',
            'cuis_id' => Cuis::inRandomOrder()->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
