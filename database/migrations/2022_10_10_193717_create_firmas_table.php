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
        Schema::create('firmas', function (Blueprint $table) {
            $table->id('id');
            $table->string('certificado', 100);
            $table->string('llave_privada', 100);
            $table->timestamp('validez');
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'REVOCADO'])->default('ACTIVO');
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
        Schema::dropIfExists('firmas');
    }
};
