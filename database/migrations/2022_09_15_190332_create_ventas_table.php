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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id');
            $table->integer('codigo_secuencia');
            $table->float('total');
            $table->float('descuento')->default(0);
            $table->timestamp('fecha');
            $table->text('descripcion')->nullable();
            $table->string('informacion_tarjeta',150)->nullable();
            $table->enum('estado', ['ACTIVO','INACTIVO','ANULADO'])->default('ACTIVO');
            $table->foreignId('metodo_pago_id')->references('id')->on('metodos_pago');
            $table->foreignId('sucursal_id')->references('id')->on('sucursales');
            $table->foreignId('cliente_id')->references('id')->on('clientes');
            $table->foreignId('punto_venta_id')->references('id')->on('puntos_venta');
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
        Schema::dropIfExists('ventas');
    }
};
