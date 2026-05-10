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
        Schema::create('producto_atributo', function (Blueprint $table) {
            $table->foreignId('atributo_id')->references('id')->on('atributos');
            $table->string('producto_id',30);
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->string('valor', 150);
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
        Schema::dropIfExists('producto_atributo');
    }
};
