<?php

namespace Database\Seeders;

use App\Models\ValorCatalogo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ValorCatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = Storage::disk('public')->get('/json/valores_catalogo.json');
        $valoresCatalogos = json_decode($json, true);

        if( $valoresCatalogos ) {
            foreach (array_chunk($valoresCatalogos, 1000) as $chunk) {
                ValorCatalogo::insert($chunk);
            }
        } else {
            ValorCatalogo::factory()->count(200)->create();
        }

    }
}
