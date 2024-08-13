<?php

namespace App\Http\trait;

trait ChanguePasswordTrait
{
    private $ASCII_NUMERO_MINIMO = 48;
    private $ASCII_NUMERO_MAXIMO = 57;
    private $ASCII_MINUSCULA_MINIMO = 97;
    private $ASCII_MINUSCULA_MAXIMO = 122;
    private $ASCII_MAYUSCULA_MINIMO = 65;
    private $ASCII_MAYUSCULA_MAXIMO = 90;

    private $INCREMENT = 5;
    public function traitChanguePassword($data) {
        $cadena = str_split( $data);
        $cadenaCopy = array();
        foreach($cadena as $char) {
            $value = ord($char);
            $valueMore = $value;
            switch ($value) {
                case $this->isNumber($value) :
                    $valueMore = ord($char) + $this->INCREMENT;
                    $valueMore = $this->charProcesado($valueMore, $this->ASCII_NUMERO_MINIMO, $this->ASCII_NUMERO_MAXIMO);
                    break;
                case $this->isMinuscula($value) :
                    $valueMore = ord($char) + $this->INCREMENT;
                    $valueMore = $this->charProcesado($valueMore, $this->ASCII_MINUSCULA_MINIMO, $this->ASCII_MINUSCULA_MAXIMO);
                    break;
                case $this->isMayuscula($value) :
                    $valueMore = ord($char) + $this->INCREMENT;
                    $valueMore = $this->charProcesado($valueMore, $this->ASCII_MAYUSCULA_MINIMO, $this->ASCII_MAYUSCULA_MAXIMO);
                    break;
            }
            $cadenaCopy[] = chr($valueMore);
        }
        return implode($cadenaCopy);
    }

    private function charProcesado($value, $min, $max) {
        if ($value > $max) {
            return $value - $max - 1 + $min;
        } else {
            return $value;
        }
    }

    private function isNumber($value) {
        return $value >= $this->ASCII_NUMERO_MINIMO && $value <= $this->ASCII_NUMERO_MAXIMO;
    }

    private function isMinuscula($value) {
        return $value >= $this->ASCII_MINUSCULA_MINIMO && $value <= $this->ASCII_MINUSCULA_MAXIMO;
    }

    private function isMayuscula($value) {
        return $value >= $this->ASCII_MAYUSCULA_MINIMO && $value <= $this->ASCII_MAYUSCULA_MAXIMO;
    }
}
