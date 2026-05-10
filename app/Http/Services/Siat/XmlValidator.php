<?php

namespace App\Http\Services\Siat;

use DOMDocument;
 /**
  * Validacion de xml contra XSD.
  */
class XmlValidator
{
    /**
     * Instancia de DOMDocument.
     *
     * @var DOMDocument instance
     */
    private $domDocument;

    function __construct()
    {
        $this->domDocument = new DOMDocument('1.0', 'utf-8');
    }

    /**
     * Validar archivo XML contra XSD, para verificar que el archivo este bien formado.
     * 
     * @param string $xml   Archivo XML a comprobar.
     * @param string $xsd   Esquema de validacion.
     * 
     * @return bool
     */
    public function validate(string $xml, string $xsd) {
        if( !file_exists($xml) ) {
            throw new \Exception(sprintf('XML file `%s` not found.', $xml));
        }

        if( !file_exists($xsd) ) {
            throw new \Exception(sprintf('XSD file `%s` not found.', $xsd));
        }

        //Deshabilite los errores de libxml y permita que el usuario obtenga información de error según sea necesario
        libxml_use_internal_errors(true);
        //Lee archivo XML
        $file = fopen($xml, 'r');
        $contents = fread($file, filesize($xml));
        $this->domDocument->loadXML($contents, LIBXML_NOBLANKS);
        fclose($file);
        //Validando documento
        if( !$this->domDocument->schemaValidate($xsd) ) {
            return false; 
        }
        return true;
    }

    /**
     * Obteniendo errores en el procedimiento en cadena de texto.
     */
    public function getXmlErrorsString()
    {
        $errorsString = '';
        $errors = libxml_get_errors();

        if( is_null($errors) ){
            return $errorsString;
        }  

        foreach ($errors as $key => $error) {
            $level = ( $error->level === LIBXML_ERR_WARNING ? 'Warning' :  ( $error->level === LIBXML_ERR_ERROR  ? 'Error' : 'Fatal') );
            $errorsString .= sprintf("    [%s] %s", $level, $error->message);

            if($error->file) {
                $errorsString .= sprintf("    in %s (line %s, col %s)", $error->file, $error->line, $error->column);
            }

            $errorsString .= "\n";
        }
        libxml_clear_errors();
        return $errorsString;
    }
}