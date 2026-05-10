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
        Schema::create('productos', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('producto', 150);
            $table->string('descripcion', 200);
            $table->index('descripcion');
            $table->string('imagen', 100)->nullable();
            $table->string('codigo_barra', 30)->nullable();
            $table->string('codigo_qr', 30)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->foreignId('procedencia_id')->references('id')->on('procedencias');
            $table->foreignId('unidad_medida_id')->references('id')->on('unidad_medidas');
            $table->foreignId('clasificacion_producto_id')->default(1)->references('id')->on('clasificaciones_producto');
            $table->index(['producto', 'descripcion']);
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
        Schema::dropIfExists('productos');
    }
};
