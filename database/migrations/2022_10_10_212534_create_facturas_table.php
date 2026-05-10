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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id('id');
            $table->string('codigo_documento_sector', 2);
            $table->string('codigo_tipo_factura', 2);
            $table->string('numero_documento_identidad', 20);
            $table->string('codigo_documento_identidad', 20);
            $table->string('codigo_metodo_pago', 2);
            $table->string('codigo_cliente', 100);
            $table->string('razon_social', 150);
            $table->text('leyenda');
            $table->string('usuario', 150);
            $table->string('cuf', 150);
            $table->string('cafc', 50)->nullable();
            $table->text('xml');
            $table->enum('estado', ['PENDIENTE', 'RECEPCIONADO', 'ANULADO'])->default('PENDIENTE');
            $table->foreignId('venta_id')->references('id')->on('ventas');
            $table->foreignId('cufd_id')->references('id')->on('cufd');
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
        Schema::dropIfExists('facturas');
    }
};
