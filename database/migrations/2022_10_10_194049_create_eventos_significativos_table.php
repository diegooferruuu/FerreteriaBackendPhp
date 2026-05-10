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
        Schema::create('eventos_significativos', function (Blueprint $table) {
            $table->id('id');
            $table->string('cufd_evento', 100);
            $table->string('cafc', 100)->nullable();
            $table->string('descripcion', 150);
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->enum('estado', ['INICIADO', 'FINALIZADO', 'RECEPCIONADO', 'VALIDADO','OBSERVADA'])->default('INICIADO');
            $table->string('codigo_recepcion', 50)->nullable();
            $table->foreignId('evento_id')->references('id')->on('valores_catalogo');
            $table->foreignIdFor(\App\Models\Sucursal::class)->nullable()->constrained('sucursales');
            $table->foreignIdFor(\App\Models\PuntoVenta::class)->nullable()->constrained('puntos_venta');
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
        Schema::dropIfExists('eventos_significativos');
    }
};
