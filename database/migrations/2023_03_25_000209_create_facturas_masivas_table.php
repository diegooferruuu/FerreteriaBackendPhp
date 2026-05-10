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
        Schema::create('facturas_masivas', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\EmisionMasiva::class)->constrained('emisiones_masivas');
            $table->foreignIdFor(\App\Models\Factura::class)->constrained('facturas');
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
        Schema::dropIfExists('masiva_facturas');
    }
};
