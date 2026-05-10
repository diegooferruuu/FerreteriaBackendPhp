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
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id');
            $table->integer('cantidad')->nullable()->default(0);
            $table->integer('cantidad_maxima')->nullable()->default(0);
            $table->integer('cantidad_minima')->nullable()->default(0);
            $table->string('producto_id',30);
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->foreignId('sucursal_id')->references('id')->on('sucursales');
            $table->unique(['producto_id','sucursal_id']);
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
        Schema::dropIfExists('inventario');
    }
};
