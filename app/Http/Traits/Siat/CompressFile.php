<?php

namespace App\Http\Traits\Siat;

use PharData;

use function PHPUnit\Framework\throwException;

/**
 * Compresion de archivos
 */
trait CompressFile
{
    /**
     * comprimir archivo a gzip
     *
     * @param string $source    Path del archivo a comprimir
     * @param integer $level    Nivel de compresion
     * @return string           Path del archivo compreso
     */
    function gzipCompressFile($source, $level = 9){
        $destino = $source . '.gz';
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($destino, $mode)) {
            if ($fp_in = fopen($source,'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error)
            return false;
        else
            return $destino;
    }

    /**
     * Empaquetar archivo
     *
     * @param string $tarFilePath   Path del archivo tar
     * @param array $files          Archivos que se deben agregar dentro del tar
     * @return void
     */
    function tarPackFile($tarFilePath, $files) {

        try {
            $tar = new PharData($tarFilePath);
            foreach ($files as $file) {
                $tar->addFile($file['path'], $file['name']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

    }
}
