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
        Schema::create('cuis', function (Blueprint $table) {
            $table->id('id');
            $table->string('valor', 50)->unique();
            $table->timestamp('validez');
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->foreignId('sucursal_id')->nullable()->references('id')->on('sucursales');
            $table->foreignId('punto_venta_id')->nullable()->references('id')->on('puntos_venta');
            $table->unique(['valor', 'sucursal_id', 'punto_venta_id']);
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
        Schema::dropIfExists('cuis');
    }
};
