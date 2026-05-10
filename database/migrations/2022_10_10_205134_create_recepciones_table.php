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
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id('id');
            $table->enum('tipo', ['INDIVIDUAL', 'CONTINGENCIA', 'MASIVA']);
            $table->enum('codigo_emision', [1, 2,3]);
            $table->timestamp('fecha_envio');
            $table->text('archivo')->nullable();
            $table->string('hash_archivo', 64);
            $table->integer('cantidad_facturas');
            $table->tinyInteger('codigo_estado');
            $table->string('codigo_recepcion', 50);
            $table->longText('mensaje_observacion')->nullable();
            $table->string('codigo_descripcion', 50);
            $table->string('codigo_documento_sector', 10);
            $table->string('codigo_documento_fiscal', 10);
            $table->foreignId('evento_significativo_id')->nullable()->references('id')->on('eventos_significativos');
            $table->foreignIdFor(\App\Models\EmisionMasiva::class)->nullable()->constrained('emisiones_masivas');
            $table->foreignId('sucursal_id')->nullable()->references('id')->on('sucursales');
            $table->foreignId('punto_venta_id')->nullable()->references('id')->on('puntos_venta');
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
        Schema::dropIfExists('recepciones');
    }
};
