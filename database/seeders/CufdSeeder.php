<?php

namespace Database\Seeders;

use App\Models\Cufd;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CufdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cufd::factory()->count(10)->create();
    }
}
