<?php

namespace App\Http\Services\Soap;

use SoapClient;


/**
 * Consumir servicios web con SoapClient.
 */
class SoapClientService extends BaseSoapService
{
    /**
     * instancia SoapClient.
     *
     * @var SoapClient instance
     */
    protected $client;

    /**
     * Metodo o nombre de funcion a llamar.
     *
     * @var string
     */
    protected string $method;

    /**
     * Parametros para llamar a la funcion del servicio web.
     *
     * @var array
     */
    protected $parameters;

    public function __construct($client = null, $parameters = [])
    {
        $this->client = $client;
        $this->parameters = $parameters;
    }

    /**
     * establecer la url del servicio web.
     *
     * @param string $wsdl
     * @return void
     */
    public function init($wsdl) {
        self::setWsdl($wsdl);
    }

    /**
     * Obtener SoapClient instanciado.
     *
     * @return SoapClient instance
     */
    public function client()
    {
        return $this->client ??= $this->constructClient();
    }

    /**
     * Construir o crear instancia de SoapClient.
     *
     * @return SoapClient instance
     */
    public function constructClient()
    {
        $soapClientOptions = [
            'stream_context' => self::generateContext(),
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            'exceptions' => true,
            'connection_timeout' => 15
        ];

        $this->client ??= new SoapClient(self::getWsdl(), $soapClientOptions);

        return $this->client;
    }

    /**
     * Obtener todas las funciones del servicio web.
     *
     * @return array
     */
    public function functions(): array
    {
        return $this->client()->__getFunctions();
    }

    /**
     * Llamar a una funcion del servicio web.
     *
     * @param string $method    Nombre del metodo o funcion a llamar.
     * @param array $parameters Argumentos necesarios para llamar a la function
     * @return mixed
     */
    public function call($method, $parameters = [],$wsdl)
    {
        $this->method = $method;
        $this->parameters = $parameters;
        try {
            return $this->client()->__soapCall($method, array($parameters));
        } catch (\SoapFault $e) {
            throw new \Exception("Error de conexion al servicio {$wsdl} espere un momento!!, o si esta realizando una factura registre un evento.", 503);
        }
    }
}
