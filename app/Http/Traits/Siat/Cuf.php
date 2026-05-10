<?php

namespace App\Http\Traits\Siat;

use Illuminate\Support\Arr;

/**
 * Generacion de codigo CUF
 */
trait Cuf
{
    use Module11, Base16;

    /**
     * Longitud a completar en los parametros. 
     *
     * @var array
     */
    private $parameterLenghts = [
        'nit' => 13,
        'fechaHora' => 17,
        'sucursal' => 4,
        'modalidad' => 1,
        'tipoEmision' => 1,
        'documentoFiscal' => 1,
        'documentoSector' => 2,
        'numeroFactura' => 10,
        'pos' => 4,
    ];

    /**
     * Generar Codigo Unico de Factura
     * 
     * @param nit               NIT emisor
     * @param fechaHora         Fecha y Hora en formato yyyyMMddHHmmssSSS
     * @param sucursal          Codigo de sucursal
     * @param modalidad         Codigo de Modalidad
     * @param tipoEmision       Codigo Tipo de Emision
     * @param documentoFiscal   Codigo Documento Fiscal(Tipo Factura Documento)
     * @param documentoSector   Codigo Tipo Documento Sector
     * @param numeroFactura     Numero de Factura
     * @param pos               Codigo Punto de Venta
     * @return string              Codigo Unico de Factura
     */
    public function generateCuf(string $nit, string $fechaHora, string $sucursal, string $modalidad, string $tipoEmision, string $documentoFiscal, string $documentoSector, string $numeroFactura, string $pos) {
        
        $zeroFillParameters = Arr::map($this->parameterLenghts, function($paramLength, $key) use ($nit, $fechaHora, $sucursal, $modalidad, $tipoEmision, $documentoFiscal, $documentoSector, $numeroFactura, $pos) {
            return zeroFill(${$key}, $paramLength);
        });
        
        $cuf = implode('', $zeroFillParameters);
        
        $cuf .= $this->getModule11($cuf);

        return $this->getBase16($cuf);
    }
}