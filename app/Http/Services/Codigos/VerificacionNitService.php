<?php

namespace App\Http\Services\Codigos;

use App\Http\Services\Siat\CodeObtaining;
use App\Models\Cuis;
use App\Models\Sucursal;

class VerificacionNitService
{
    protected $serviceCode;

    public function __construct()
    {
        $this->serviceCode = new CodeObtaining();
    }

    public function handleStore($params)
    {

//        $dataCuis = Cuis::select('id','valor')->where('sucursal_id',$params['sucursal_id'])->with('sucursal:id:codigo_siat')->first();
$dataCuis = Cuis::select('id','valor')
            ->where('sucursal_id', $params['sucursal_id'])
            ->with('sucursal:id,codigo_siat')
            ->latest('id') // o ->orderBy('id','desc')
            ->first();
        $dataCuis['cedula_nit'] = $params['cedula_nit'];

//        if( !is_null($cuis->sucursal_id) ) {
//            $branchCode = $cuis->sucursal->codigo_siat;
//            $posCode = 0;
//        }

        $response = $this->serviceCode->requestVerificacionNit($dataCuis);

        return $response;
    }


}
