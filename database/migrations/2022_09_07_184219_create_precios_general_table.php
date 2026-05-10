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
        Schema::create('precios_general', function (Blueprint $table) {
            $table->id('id');
            $table->float('precio_menor');
            $table->float('descuento_menor')->default(0);
            $table->float('precio_mayor');
            $table->float('descuento_mayor')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->string('producto_id',30)->unique();
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->foreignId('carga_precio_id')->nullable()->references('id')->on('carga_precios');
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
        Schema::dropIfExists('precios_general');
    }
};
