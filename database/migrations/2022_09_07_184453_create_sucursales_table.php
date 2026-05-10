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
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id('id');
            $table->tinyInteger('codigo_siat')->unique();
            $table->string('nombres', 150);
            $table->string('abreviatura', 50);
            $table->string('direccion', 100);
            $table->string('latitud', 30)->nullable();
            $table->string('longitud', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->foreignId('departamento_id')->references('id')->on('departamentos');
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
        Schema::dropIfExists('sucursales');
    }
};
