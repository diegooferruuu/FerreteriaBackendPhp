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
        Schema::create('sincronizacion_catalogos', function (Blueprint $table) {
            $table->id('id');
            $table->integer('syncable_id');
            $table->string('syncable_type');
            $table->foreignId('catalogo_facturacion_id')->references('id')->on('catalogos_facturacion');
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
        Schema::dropIfExists('sincronizacion_catalogos');
    }
};
