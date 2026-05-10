<?php

namespace App\Http\Services;

use App\Models\Firma;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gestion de firma digital
 */
class FirmaService
{
    /**
     * Firma digital activo
     *
     * @var Firma
     */
    protected $firmaDigital;

    public function __construct()
    {
        $this->firmaDigital = Firma::where('estado', 'ACTIVO')->firstOrFail();
//        dd( Storage::disk('private')->get($this->firmaDigital->llave_privada) );
    }

    /**
     * Obtener contenido de certificado .crt y llave privada .key
     *
     * @return string pfx
     */
    public function getPfxContent() {
        $llavePrivada = Storage::exists('private/'.$this->firmaDigital->llave_privada);
        $certificado =  Storage::exists('private/'.$this->firmaDigital->certificado);
        if($llavePrivada === false || $certificado ===false)
        {
          return false;
        }else{
            $contentKey = Storage::disk('private')->get($this->firmaDigital->llave_privada);
            $contentCert = Storage::disk('private')->get($this->firmaDigital->certificado);
            $contentPfx = Str::of($contentKey)->append($contentCert);
            return $contentPfx;
        }

    }
}
