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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id');
            $table->string('razon_social', 150);
            $table->string('cedula_nit', 20)->unique();
            $table->string('complemento', 20)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->boolean('verificacion')->nullable()->default(0); //verificacion de nit, si esta en 0 es que es otro tipo de documento si es 1 se verifico nit
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->foreignId('departamento_id')->references('id')->on('departamentos');
            $table->foreignId('tipo_documento_id')->references('id')->on('valores_catalogo');
//             $table->tinyInteger('tipo_documento_id');
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
        Schema::dropIfExists('clientes');
    }
};
