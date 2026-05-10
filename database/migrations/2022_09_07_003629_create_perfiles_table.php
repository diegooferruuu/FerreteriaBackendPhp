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
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id('id');
            $table->string('nombres', 150);
            $table->string('apellidos', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('foto', 100)->nullable();
            $table->foreignId('usuario_id')->unique()->references('id')->on('usuarios')->onDelete('cascade');
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
        Schema::dropIfExists('perfiles');
    }
};
