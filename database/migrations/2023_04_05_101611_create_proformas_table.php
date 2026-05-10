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
        Schema::create('proformas', function (Blueprint $table) {
            $table->id('id');
            $table->integer('codigo_secuencia');
            $table->float('total');
            $table->float('descuento')->default(0);
            $table->float('descuentoMonto')->default(0);
            $table->timestamp('fecha');
            $table->date('vigencia');
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['ACTIVO','INACTIVO','ANULADO'])->default('ACTIVO');
            $table->foreignId('sucursal_id')->references('id')->on('sucursales');
            $table->foreignId('cliente_id')->references('id')->on('clientes');
            $table->timestamps();
//            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proformas');
    }
};
