<?php

namespace Database\Seeders;

use App\Models\Atributo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class AtributoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Atributo::create([
            'atributo' => 'Unidad',
        ]);
//        Atributo::factory()->count(3)->state(new Sequence(
//            ['atributo' => 'Unidad'],
//        ))->create();
    }
}
