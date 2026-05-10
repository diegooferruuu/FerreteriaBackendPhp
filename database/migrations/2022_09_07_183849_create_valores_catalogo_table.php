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
        Schema::create('valores_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_clasificador', 100);
            $table->string('codigo_actividad', 100)->nullable();
            $table->text('descripcion');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->foreignId('sincronizacion_catalogo_id')->references('id')->on('sincronizacion_catalogos');
            $table->unique(['codigo_clasificador', 'sincronizacion_catalogo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valores_catalogo');
    }
};
