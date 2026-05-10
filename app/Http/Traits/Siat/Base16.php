<?php
namespace App\Http\Traits\Siat;
/**
 * Conversion a base16.
 */
trait Base16
{
    /**
     * Obtener base16 de una cadena de texto.
     *
     * @param string $value
     * @param boolean $toUppercase
     * @return string
     */
    public function getBase16(string $value, bool $toUppercase = true)
    {
        $hexadecimalValues = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'];
        $base16 = '';

        while ($value != 0) {
//            dd(bcmod('1155285','16'));
            $base16 = $hexadecimalValues[bcmod($value, '16')] . $base16;

            $value = bcdiv($value, '16', 0);
        }

        return ($toUppercase) ? strtoupper($base16) : $base16;
    }
}
