<?php
// used in Filter
if ( !function_exists('separateCommaValues') ) {
    function separateCommaValues($value)
    {
        return explode(',', $value);
    }
}

if ( !function_exists('hasComma') ) {
    function hasComma($value)
    {
        return strpos($value, ',');
    }
}

if ( !function_exists('isDateTime') ) {
    function isDateTime($value)
    {
        return date_parse($value)['error_count'] < 1;
    }
}
// end

// used in Siat
if( !function_exists('zeroFill') ) {
    function zeroFill($value, $quantity) {
        return str_pad($value, $quantity, 0, STR_PAD_LEFT);
    }
}
if( !function_exists('dateFormatUtcExtended') ) {
    function dateFormatUtcExtended($value) {
        return ( new DateTime($value) )->format('Y-m-d\TH:i:s.v');
    }
}

if( !function_exists('obfuscate') ) {
    function obfuscate($value, $character, $left = 0, $right = 0) {
        $length = Illuminate\Support\Str::length($value);
        return Illuminate\Support\Str::mask($value, $character, $left - $length, $length - $left - $right);
    }
}
// end

if( !function_exists('isArrayMultidimensional') ) {
    function isArrayMultidimensional( $array ) {
        rsort( $array );
        return isset( $array[0] ) && is_array( $array[0] );
    }
}
