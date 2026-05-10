<?php

namespace App\Http\Services\Soap;

/**
 * Parametros basicos para consumir servicios web
 */
class BaseSoapService
{
    /**
     * Url del servicio web
     *
     * @var string
     */
    protected static $wsdl;

    /**
     * Token para consumir el servicio web (opcional)
     *
     * @var string
     */
    protected static $token;

    /**
     * Opciones de contexto de transmision.
     *
     * @var array
     */
    protected static $options;

    /**
     * Contexto de transmision
     *
     * @var resource
     */
    protected static $context;

    public static function setWsdl($wsdl) {
        self::$wsdl = $wsdl;
    }

    public static function getWsdl() {
        return self::$wsdl;
    }

    public static function setToken($token) {
        self::$token = $token;
    }

    public static function getToken() {
        return self::$token;
    }

    public static function setOptions($options) {
        self::$options = $options;
    }

    public static function getOptions() {
        return self::$options;
    }

    /**
     * Generar contexto de transmision
     *
     * @return resource
     */
    public static function generateContext() {
        self::$options = [
            'http' => [
                'header' => 'apikey:TokenApi ' . self::getToken(),
            ]
        ];

        return self::$context = stream_context_create(self::$options);
    }
}