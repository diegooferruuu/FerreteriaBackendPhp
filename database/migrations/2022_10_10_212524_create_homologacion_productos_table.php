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
        Schema::create('homologacion_productos', function (Blueprint $table) {
            $table->id('id');
            $table->string('codigo_siat', 50);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->string('producto_id',30)->unique();
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->foreignId('catalogo_producto_id')->references('id')->on('valores_catalogo');
            // $table->unique(['producto_id','catalogo_producto_id']);
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
        Schema::dropIfExists('homologacion_productos');
    }
};
