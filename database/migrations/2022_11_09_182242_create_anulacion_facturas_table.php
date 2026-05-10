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
        Schema::create('anulacion_facturas', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('factura_id')->references('id')->on('facturas');
            $table->foreignId('motivo_id')->references('id')->on('valores_catalogo');
            $table->string('descripcion', '150')->nullable();
            $table->tinyInteger('codigo_estado');
            $table->string('codigo_descripcion', 50);
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
        Schema::dropIfExists('anulacion_facturas');
    }
};
