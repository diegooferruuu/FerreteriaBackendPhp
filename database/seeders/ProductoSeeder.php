<?php

namespace Database\Seeders;

use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productos = Producto::factory()->count(10)->create();
        /* $chunks = $productos->chunk(500);
        $chunks->each(function($chunk) {
            DB::table('productos')->insert($chunk->toArray());
        }); */
    }
}
