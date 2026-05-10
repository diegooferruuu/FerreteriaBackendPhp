<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('puntos_venta', function (Blueprint $table) {
            $table->id('id');
            $table->tinyInteger('codigo_siat')->unique();
            $table->string('nombre', 150)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->foreignId('tipo_punto_venta_id')->nullable()->references('id')->on('valores_catalogo');
//            $table->tinyInteger('tipo_punto_venta_id')->nullable();
            $table->foreignId('sucursal_id')->references('id')->on('sucursales');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('puntos_venta');
    }
};
