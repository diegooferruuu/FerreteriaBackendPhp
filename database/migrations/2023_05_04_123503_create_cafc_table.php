<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cafc', function (Blueprint $table) {
            $table->id();
            $table->string('cafc',50);
            $table->integer('numero_inicio');
            $table->integer('numero_fin');
            $table->integer('numero_facturas_utilizadas')->default(0);
            $table->enum('estado', ['VALIDO','INVALIDO'])->default('VALIDO');
            $table->foreignId('sucursal_id')->references('id')->on('sucursales');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cafc');
    }
};
