<?php

namespace App\Http\Traits\Siat;

/**
 * Cifrado de archivo
 */
trait Hasher
{
    /**
     * Cifrar con hash256
     *
     * @param string $filePath  Path del archivo a sifrar
     * @return string
     */
    public function hash256($filePath)
    {
        // file_put_contents('example.txt', 'The quick brown fox jumped over the lazy dog.'); escribe en un archivo. Crea el archivo si no existe
        if( ! is_string($filePath) ) {
            throw new \Exception("filePath debe ser una cadena");
            
        }
        
        if( ! file_exists($filePath) ) {
            throw new \Exception("Archivo no encontrado");
            
        }

        return strtoupper( hash_file('sha256', $filePath) );
    }
}