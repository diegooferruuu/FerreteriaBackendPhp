<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\Firma;
use App\Models\SincronizacionCatalogo;
use App\Models\TokenDelegado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlertasController extends Controller
{
    use ApiResponser;
    public function alertasSiat()
    {
        //5 dias antes
        $dataFirma = Firma::select('id', 'validez')->where('estado','ACTIVO')->get();
        //5 dias antes
        $dataToken = TokenDelegado::select('id', 'validez')->where('estado','ACTIVO')->get();
        //notificacion 5 dias antes
        $dataCuis = Cuis::select('id','validez','sucursal_id','punto_venta_id')->where('estado','ACTIVO')->get();
        //avisar si esta por vencer 1 hora antes
        $dataCufd = Cufd::select('id', 'validez','cuis_id','created_at')->where('estado','ACTIVO')
            ->get()
            ->each(function ($query,$key){
//                $query['validez'] = Carbon::parse($query->validez)->format('Y-m-d H:i:s');
                $query['created_at'] = Carbon::parse($query->created_at)->format('Y-m-d H:i:s');
            });

        $dataCatalogo = SincronizacionCatalogo::with('valores')->where('syncable_id',1)->where('syncable_type','sucursal')->latest()->first();
        $valorCatalogoUltimaSincronizacion = null;
        if (!is_null($dataCatalogo))
        {
            $valorCatalogoUltimaSincronizacion = $dataCatalogo->valores->first();
        }

        $data = [
            'cufd' => count($dataCufd) != 0 ? $dataCufd : null ,
            'cuis' => count($dataCuis) != 0 ? $dataCuis : null ,
            'token' => count( $dataToken) != 0 ? $dataToken : null ,
            'firma' =>count( $dataFirma) != 0 ? $dataFirma : null ,
            'sincronizacionCatalogo' => $valorCatalogoUltimaSincronizacion ?  [
                'syncable_id' => 1,
                'syncable_type' => 'sucursal',
                'ultima_sincronizacion_catalogo' => Carbon::parse($valorCatalogoUltimaSincronizacion->created_at)->format('Y-m-d') ,
            ] : null
        ];

        return $this->ResponseJson($data, 'Registro recueprados!!');


    }
}
