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
        Schema::create('tipo_impresion', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['rollo','pagina'])->default('pagina');
            $table->enum('tipo_siat',[1,2])->default(2)->comment('1 es tipo rollo/ 2 tipo pagina en siat');
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
        Schema::dropIfExists('tipo_impresion');
    }
};
