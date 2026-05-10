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
        Schema::create('autorizacion_sistema', function (Blueprint $table) {
            $table->id('id');
            $table->string('nit', 20);
            $table->string('razon_social', 150);
            $table->string('nombre_comercial', 100);
            $table->string('version', 5);
            $table->enum('tipo', ['PROPIO', 'PROVEEDOR']);
            $table->string('codigo_sistema', 50);
            $table->enum('codigo_ambiente', [1, 2]);
            $table->enum('codigo_modalidad', [1, 2]);
            $table->string('logo', 150)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO']);
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
        Schema::dropIfExists('autorizacion_sistema');
    }
};
