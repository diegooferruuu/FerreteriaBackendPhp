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
        Schema::create('carga_precios', function (Blueprint $table) {
            $table->id('id');
            $table->enum('descripcion', ['mayor','menor','ambos']);
            $table->enum('estado', ['ACTIVO','INACTIVO'])->default('ACTIVO');
            $table->foreignId('subido_por')->references('id')->on('usuarios');
            $table->foreignId('autorizado_por')->nullable()->references('id')->on('usuarios');
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
        Schema::dropIfExists('carga_precios');
    }
};
