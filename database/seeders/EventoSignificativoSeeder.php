<?php

namespace Database\Seeders;

use App\Models\EventoSignificativo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventoSignificativoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EventoSignificativo::factory()->hasFacturas(10)->count(3)->create();
    }
}
