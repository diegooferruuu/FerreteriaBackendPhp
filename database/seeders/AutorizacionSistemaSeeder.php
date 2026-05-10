<?php

namespace Database\Seeders;

use App\Models\Cafc;
use App\Models\Firma;
use App\Models\Offline;
use App\Models\TipoImpresion;
use App\Models\TokenDelegado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AutorizacionSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       DB::table('autorizacion_sistema')->insert([
           'nit' => '742988014',
           'razon_social' => 'FERRUFINO GUILLEN ROMULO ROLANDO',
           'nombre_comercial' => 'AME_FACTURAS',
           'version' => '1.0',
           'tipo' => 'PROPIO',
           'codigo_sistema' => '7711F4E9F2A4567F6DBF156',
           'codigo_ambiente' => '1',
           'codigo_modalidad' => '1',
           'estado' => 'ACTIVO',
       ]);

       TokenDelegado::create([
           'valor' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiI1TElaQVJSQUdBIiwiY29kaWdvU2lzdGVtYSI6Ijc3MTFGNEU5RjJBNDU2N0Y2REJGMTU2Iiwibml0IjoiSDRzSUFBQUFBQUFBQURNM01iSzBzREF3TkFFQXNqTWIxZ2tBQUFBPSIsImlkIjoxMzU0NjMsImV4cCI6MTcyMjI5NzYwMCwiaWF0IjoxNjkwODM1NTc3LCJuaXREZWxlZ2FkbyI6NzQyOTg4MDE0LCJzdWJzaXN0ZW1hIjoiU0ZFIn0.koMqLod8jIuWSg4vENF2PQXNjt4xMgm5wYchcoIsQd6EoEOK5r8FR1AWoAGLHsWsep6Hm3pO2kYMhs01LvuC6A',
           'validez' =>'2024-07-30',
       ]);
       Firma::create([
           'certificado' =>'certificado/america.crt.pem',
           'llave_privada' =>'certificado/america.key.pem',
           'validez' => '2024-03-14 10:00:00'
       ]);

        //ITG
        // DB::table('autorizacion_sistema')->insert([
        //     'nit' => '1007679020',
        //     'razon_social' => 'IMPORTADORA TRES GENERACIONES SRL',
        //     'nombre_comercial' => 'AME_FACTURAS',
        //     'version' => '1.0',
        //     'tipo' => 'PROPIO',
        //     'codigo_sistema' => '77208445F9B19417B24B606',
        //     'codigo_ambiente' => '2',
        //     'codigo_modalidad' => '1',
        //     'estado' => 'ACTIVO',
        // ]);
        //
        // TokenDelegado::create([
        //     'valor' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiI1TElaQVJSQUdBIiwiY29kaWdvU2lzdGVtYSI6Ijc3MjA4NDQ1RjlCMTk0MTdCMjRCNjA2Iiwibml0IjoiSDRzSUFBQUFBQUFBQURNME1EQTNNN2MwTURJQUFFTV9RQUVLQUFBQSIsImlkIjoxMjkyNzMsImV4cCI6MTY5MDc2MTYwMCwiaWF0IjoxNjgyNDU3MjMzLCJuaXREZWxlZ2FkbyI6MTAwNzY3OTAyMCwic3Vic2lzdGVtYSI6IlNGRSJ9.4F-FZhX0rxtaZWjdXkXa9MVYyzFS-1ZBobna7TpK9C25ZrMZ8Phpe3NyPVBvORdgJW63X4juHZRWelbJAbIfeg',
        //     'validez' =>'2023-07-31',
        // ]);
        // Firma::create([
        //     'certificado' =>'certificado/itg.crt.pem',
        //     'llave_privada' =>'certificado/itg.key.pem',
        //     'validez' => '2024-05-31 22:16:00'
        // ]);

//        //RIVERO
//        DB::table('autorizacion_sistema')->insert([
//            'nit' => '5221314017',
//            'razon_social' => 'RIVERO FERRUFINO IGNACIO GABRIEL',
//            'nombre_comercial' => 'AME_FACTURAS',
//            'version' => '1.0',
//            'tipo' => 'PROPIO',
//            'codigo_sistema' => '77208441814C4838E6D71DE',
//            'codigo_ambiente' => '2',
//            'codigo_modalidad' => '1',
//            'estado' => 'ACTIVO',
//        ]);
//
//        TokenDelegado::create([
//            'valor' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJSaXZlcm81MjIiLCJjb2RpZ29TaXN0ZW1hIjoiNzcyMDg0NDE4MTRDNDgzOEU2RDcxREUiLCJuaXQiOiJINHNJQUFBQUFBQUFBRE0xTWpJME5qUXhNRFFIQUZiX0JwOEtBQUFBIiwiaWQiOjY1MTM2NywiZXhwIjoxNjkwNzYxNjAwLCJpYXQiOjE2ODI0NTcwOTYsIm5pdERlbGVnYWRvIjo1MjIxMzE0MDE3LCJzdWJzaXN0ZW1hIjoiU0ZFIn0.pjdrkvchj5GcQRFAf8XgIRJQQOG-48O77axMIoqRegZ39gBvRbUcYFGWKZ66Itwh4Z0_lsOqqxDnZmvkMssyLQ',
//            'validez' =>'2023-05-31',
//        ]);
//        Firma::create([
//            'certificado' =>'certificado/rivero.crt.pem',
//            'llave_privada' =>'certificado/rivero.key.pem',
//            'validez' => '2023-05-31 22:16:00'
//        ]);

        TipoImpresion::create([
            'tipo' => 'rollo',
            'tipo_siat' => 1,
        ]);
        TipoImpresion::create([
            'tipo' => 'pagina',
            'tipo_siat' => 2,
        ]);




    }
}
