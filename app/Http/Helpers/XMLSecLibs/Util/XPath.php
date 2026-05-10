<?php

namespace App\Http\Helpers\XMLSecLibs\Util;

class XPath
{
    const ALPHANUMERIC = '\w\d';
    const NUMERIC = '\d';
    const LETTERS = '\w';
    const EXTENDED_ALPHANUMERIC = '\w\d\s\-_:\.';

    const SINGLE_QUOTE = '\'';
    const DOUBLE_QUOTE = '"';
    const ALL_QUOTES = '[\'"]';


    /**
     * Filtra un valor de atributo para guardar la inclusión en una consulta XPath.
     *
     * @param string $value El valor a filtrar.
     * @param string $quotes Las comillas utilizadas para delimitar el valor en la consulta XPath.
     *
     * @return string El valor del atributo filtrado.
     */
    public static function filterAttrValue($value, $quotes = self::ALL_QUOTES)
    {
        return preg_replace('#' . $quotes . '#', '', $value);
    }


    /**
     * Filtra un nombre de atributo para guardar la inclusión en una consulta XPath.
     *
     * @param string $name El nombre del atributo a filtrar.
     * @param mixed $allow El conjunto de caracteres a permitir. Puede ser una de las constantes proporcionadas por esta clase, o una
     * expresión regular personalizada que excluye el carácter '#' (utilizado como delimitador).
     *
     * @return string El nombre del atributo filtrado.
     */
    public static function filterAttrName($name, $allow = self::EXTENDED_ALPHANUMERIC)
    {
        return preg_replace('#[^' . $allow . ']#', '', $name);
    }
}
