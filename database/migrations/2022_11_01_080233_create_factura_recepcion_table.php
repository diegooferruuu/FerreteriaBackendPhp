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
        Schema::create('factura_recepcion', function (Blueprint $table) {
            $table->foreignId('factura_id')->references('id')->on('facturas');
            $table->foreignId('recepcion_id')->references('id')->on('recepciones');
            $table->tinyInteger('codigo_estado')->nullable();
            $table->tinyInteger('nro_archivo')->nullable()->comment('Nro de archivo enviado en el paquete a SIAT por contingencia');
            $table->longText('mensaje_observacion')->nullable()->default(null);
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
        Schema::dropIfExists('factura_recepcion');
    }
};
