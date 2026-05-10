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
        Schema::create('inventario_movimiento', function (Blueprint $table) {
            $table->id('id');
            $table->float('inicial')->nullable();
            $table->float('ingresos')->nullable();
            $table->float('egresos')->nullable();
            $table->float('precio')->nullable();
            $table->string('identificador',150);
            $table->string('origen',150);
            $table->string('secuencial_origen',150);
            $table->string('observaciones',200)->nullable();
            $table->timestamp('fecha')->nullable();//fecha en la que se hizo el ingreso o egreso
            $table->foreignId('movimiento_id')->references('id')->on('movimientos');
            $table->foreignId('inventario_id')->references('id')->on('inventario');
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
        Schema::dropIfExists('inventario_movimiento');
    }
};
