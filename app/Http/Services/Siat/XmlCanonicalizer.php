<?php

namespace App\Http\Services\Siat;

use DOMDocument;

/**
 * Canonicalizar documento xml
 */
class XmlCanonicalizer
{
    /**
     * Instancia de DOMDocument
     *
     * @var DOMDocument instance
     */
    private $domDocument;

    function __construct()
    {
        $this->domDocument = new DOMDocument('1.0', 'utf-8');
    }

    /**
     * Canonicalizar cadena xml
     *
     * @param string $xml
     * @return string   Xml canonicalizado
     */
    public function canonicalize(string $xml) {
        try {
            $this->domDocument->loadXML($xml);
    
            return $this->domDocument->C14N();  
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}