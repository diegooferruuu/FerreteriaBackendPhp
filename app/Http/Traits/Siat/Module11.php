<?php

namespace App\Http\Traits\Siat;

/**
 * Calculando modulo11 de un valor dado
 */
trait Module11
{
    /**
     * Calcular modulo11
     *
     * @param string $value
     * @param int $numDig
     * @param int $limMult
     * @param boolean $x10
     * @return string
     */
    public function calculate($value, $numDig, $limMult, $x10) {

        if( !$x10 ) $numDig = 1;

        for ($n = 1; $n <= $numDig; $n++) { 
            $suma = 0;
            $mult = 2;
            
            for ($i = strlen($value) - 1; $i >= 0; $i--) { 
                $suma += ( $mult * ( (int) substr($value, $i, 1) ) );
                if( ++$mult > $limMult ) $mult = 2;
            }

            if($x10) {
                $dig = ( ($suma * 10) % 11) % 10;
            } else {
                $dig = $suma % 11;
            }
            
            if($dig == 10) {
                $value .= "1"; 
            }

            if($dig == 11) {
                $value .= "0";
            }

            if($dig < 10) {
                $value .= $dig;
            }
        }
        
        return substr($value, strlen($value) - $numDig, $numDig);
    }

    /**
     * Obtener modulo11.
     *
     * @param string $value
     * @return void
     */
    public function getModule11($value) {
        return  $this->calculate($value, 1, 9, false);
    }        
}