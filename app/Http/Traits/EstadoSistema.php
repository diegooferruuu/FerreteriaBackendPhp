<?php

namespace App\Http\Traits;

use App\Models\Offline;
use Illuminate\Http\Response;

trait EstadoSistema
{
//    public function getEstadoSistema()
//    {
//        return Offline::select('estado')->first()->estado;
//    }
    public function changeSystemStateOffline()
    {
//        Offline::where('id',1)->update(['estado' => 'OFFLINE']);
        return $this->getEstadoSistema();
    }

    public function changeSystemStateOnline()
    {
//        Offline::where('id',1)->update(['estado' => 'ONLINE']);
        return $this->getEstadoSistema();
    }
}

