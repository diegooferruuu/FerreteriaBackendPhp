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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id');
            $table->string('username', 150)->unique();
            $table->string('password', 60);
            $table->string('email', 80)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('superAdmin')->default(false);
            $table->unsignedTinyInteger('resent')->default(0);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); //Nueva línea, para el borrado lógico
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
};
