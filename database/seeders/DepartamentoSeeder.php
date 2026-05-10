<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $dataDepartments =  [
            [
                'departamento' => 'Cochabamba',
            ],
            [
                'departamento' => 'Chuquisaca',
            ],
            [
                'departamento' => 'La Paz',
            ],
            [
                'departamento' => 'Oruro',
            ],
            [
                'departamento' => 'Potosí',
            ],
            [
                'departamento' => 'Tarija',
            ],
            [
                'departamento' => 'Santa Cruz',
            ],
            [
                'departamento' => 'Beni',
            ],
            [
                'departamento' => 'Pando',
            ]
        ];
        foreach($dataDepartments as $data){
            Departamento::create($data);
        }
    }
}
