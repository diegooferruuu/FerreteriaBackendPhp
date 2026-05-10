<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::unprepared('
            CREATE OR REPLACE TRIGGER tg_after_insert_precio_gral
            AFTER INSERT ON precios_general
                FOR EACH ROW EXECUTE FUNCTION fn_tg_after_insert_precio_gral();
        ');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tg_after_insert_precio_gral ON precios_general;');
    }
};
