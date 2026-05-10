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
        Schema::create('detalle_proforma', function (Blueprint $table) {
            $table->float('cantidad');
            $table->float('precio');
            $table->float('descuento')->default(0);
            $table->float('descuentoMonto')->default(0);
            $table->float('sub_total');
            $table->string('codigo_producto_mayor_menor',30); //isMayor //isMenor
            $table->string('producto_id',30);
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->foreignIdFor(\App\Models\Proforma::class)->constrained('proformas');
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
        Schema::dropIfExists('detalle_proforma');
    }
};
