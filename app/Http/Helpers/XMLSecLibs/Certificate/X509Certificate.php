<?php

namespace App\Http\Helpers\XMLSecLibs\Certificate;

use DateTime;
use Exception;

/**
 * Class X509Certificate
 */
class X509Certificate
{
    /**
     * @var string
     */
    private $pfx;
    /**
     * @var array
     */
    private $certs;
    /**
     * @var array
     */
    private $subject;

    /**
     * X509Certificate constructor.
     * @param string $pfx
     * @param string $password
     * @throws Exception
     */
    public function __construct($pfx, $password)
    {
        $this->pfx = $pfx;
        $this->parsePfx($pfx, $password);
    }

    /**
     * @param string $filename
     * @param string $password
     * @return X509Certificate
     * @throws Exception
     */
    public static function createFromFile($filename, $password)
    {
        if (!file_exists($filename)) {
            throw new Exception('Archivo de certificado no encontrado');
        }
        $content = file_get_contents($filename);

        return new X509Certificate($content, $password);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->getSubjectValue('name');
    }

    /**
     * @return array|null
     */
    public function getSubject()
    {
        return $this->getSubjectValue('subject');
    }

    /**
     * @return array|null
     */
    public function getIssuer()
    {
        return $this->getSubjectValue('subject');
    }

    /**
     * El certificado es válido a partir de esta fecha.
     *
     * @return DateTime|null
     */
    public function getValidFrom()
    {
        return $this->getSubjectDateValue('validFrom_time_t');
    }

    /**
     * El certificado es válido hasta la fecha.
     *
     * @return DateTime|null
     */
    public function getExpiration()
    {
        return $this->getSubjectDateValue('validTo_time_t');
    }

    /**
     * @return array|null
     */
    public function getPurposes()
    {
        return $this->getSubjectValue('purposes');
    }

    /**
     * @return array|null
     */
    public function getExtensions()
    {
        return $this->getSubjectValue('extensions');
    }

    /**
     * Obtener clave pública.
     *
     * @return string|null
     */
    public function getPublicKey()
    {
        return isset($this->certs['cert']) ? $this->certs['cert'] : null;
    }

    /**
     * Obtener clave privada.
     *
     * @return string|null
     */
    public function getPrivateKey()
    {
        return isset($this->certs['pkey']) ? $this->certs['pkey'] : null;
    }

    public function getRaw()
    {
        return $this->pfx;
    }

    /**
     * Exporte el certificado actual.
     *
     * @param int $type
     * @return string|null
     */
    public function export($type)
    {
        switch ($type) {
            case X509ContentType::PEM:
                return $this->getPublicKey() . $this->getPrivateKey();
            case X509ContentType::CER:
                return $this->getPublicKey();
        }

        return '';
    }

    /**
     * Convierte un Almacén de Certificado PKCS#12 a una matriz
     * @param $pfx
     * @param $password
     * @throws Exception
     */
    private function parsePfx($pfx, $password)
    {
        $result = openssl_pkcs12_read($pfx, $certs, $password);

        if ($result === false) {
            throw new Exception(openssl_error_string());
        }

        $this->certs = $certs;
    }

    /**
     * Analiza un certificado X509 y devuelve la información como un matriz.
     * @return array|null
     */
    private function loadSubject()
    {
        if ($this->subject) {
            return;
        }

        $this->subject = openssl_x509_parse($this->getPublicKey());
    }

    /**
     * Obtiene propiedades del certificado
     * @param string $key
     * @return mixed|null
     */
    private function getSubjectValue($key)
    {
        $this->loadSubject();

        if (isset($this->subject[$key])) {
            return $this->subject[$key];
        }

        return null;
    }

    /**
     * Obtiene fecha valido desde hasta
     * @param string $key
     * @return mixed|null
     */
    private function getSubjectDateValue($key)
    {
        $value = $this->getSubjectValue($key);
        if ($value) {
            return (new DateTime())->setTimestamp($value);
        }

        return $value;
    }
}
