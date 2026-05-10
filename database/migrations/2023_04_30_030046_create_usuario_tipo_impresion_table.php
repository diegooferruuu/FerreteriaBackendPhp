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
        Schema::create('usuario_tipo_impresion', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Usuario::class)->constrained('usuarios');
            $table->foreignIdFor(\App\Models\TipoImpresion::class)->constrained('tipo_impresion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_tipo_impresion');
    }
};
