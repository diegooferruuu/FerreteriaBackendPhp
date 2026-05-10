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
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_tg_after_insert_precio_gral() RETURNS TRIGGER AS $$
                DECLARE
                    inv_id INTEGER;
                BEGIN
                    --
                    -- Eliminar logicamente el precio general anterior del producto cuando se crea uno nuevo.
                    --
                    UPDATE precios_general
                    SET deleted_at = now()
                    WHERE precios_general.id != NEW.id AND precios_general.producto_id = NEW.producto_id AND precios_general.deleted_at IS NULL;


                    --
                    -- Insertar producto para todas las sucursales(activas) en inventario si no existe(producto_id, sucursal_id)
                    --
                    INSERT INTO inventario (producto_id,cantidad, sucursal_id, created_at, updated_at)
                    SELECT NEW.producto_id, 2000000000, id, now(), now() FROM sucursales WHERE sucursales.estado = 'ACTIVO' AND sucursales.deleted_at IS NULL
                    ON CONFLICT (producto_id, sucursal_id) DO NOTHING
                    RETURNING id INTO inv_id;

                    INSERT INTO inventario_movimiento (inicial, identificador,origen,secuencial_origen,movimiento_id,inventario_id, created_at, updated_at)
                    VALUES ( 2000000000,'inicial','inv-inicial' ,'ini-1',1,inv_id, now(),now() );

                    RETURN NULL;
                END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS fn_tg_after_insert_precio_gral;");
    }
};
