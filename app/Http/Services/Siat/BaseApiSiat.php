<?php

namespace App\Http\Services\Siat;

use App\Http\Services\Soap\SoapClientService;
use App\Models\TokenDelegado;

/**
 * Clase para conectarse a servicio web de SIAT.
 */
class BaseApiSiat
{
    /**
     * instancia de SoapClientService class.
     *
     * @var SoapClientService instance
     */
    protected $serviceClient;

    /**
     * Token delegado emitido por SIN para usar servicios web SIAT.
     *
     * @var string
     */
    protected $tokenDelegado;

    /**
     * @param string $wsdl
     * @param array $availableFunctions
     *
     * @return void
     */
    public function __construct(string $wsdl, array $availableFunctions)
    {
        /**
         * Url del servicio web.
         *
         * Establezca la variable de entorno SIAT_API_URL en .ENV
         * @example SIAT_API_URL=https://pilotosiatservicios.impuestos.gob.bo/v2/
         */
        $this->wsdl = config('app.siat_api_url') . $wsdl;

        /**
         * Funciones disponibles del servicio web a consumir.
         */
        $this->availableFunctions = $availableFunctions;

        // Otener token delegado
        $this->tokenDelegado = TokenDelegado::where('estado', 'ACTIVO')->firstOrFail();
        // inicializar Soap Client
        $this->serviceClient = new SoapClientService();
        $this->serviceClient->setToken($this->tokenDelegado->valor);
        $this->serviceClient->init($this->wsdl);
    }

    /**
     * Inicia el metodo solicitado validando que el metodo este disponible en el servicio web SIAT.
     * @param mixed $method     Nombre del metodo a consultar
     * @param mixed $arguments  Argumentos o parametros que necesita el método
     *
     * @return mixed
     */
    public function __call($method, $arguments) {

        //Obteniendo los parametros enviados(__call envuelve dentro de un array [] a los argumentos)
        $arguments = empty($arguments) ? null : $arguments[0];

        if( method_exists($this, $method) ) {
            return $this->{$method}($arguments);
        }

        if( !in_array( $method, array_keys($this->availableFunctions) ) ) {
            throw new \Exception("Método {$method} no valido!");
        }

        return $this->call($method, $arguments);
    }

    /**
     * Obtiene todas las funciones sel servicio web SIAT.
     *
     * @return array
     */
    protected function functions() {
        return $this->serviceClient->functions();
    }

    /**
     * Llamar a una funcion del servicio web SIAT.
     *
     * @param string $method    Nombre de la funcion SOAP a llamar.
     * @param array $params     Una matriz de los argumentos para pasar a la funcion.
     *
     * @return mixed
     */
    private function call($method, $params) {
        return $this->serviceClient->call($method, $params,$this->wsdl);
    }
}
