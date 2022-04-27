<?php
/**
 * Number object
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class Number implements \JsonSerializable
{
    private $value;
    private $decimal;
    private $final;

    public function __construct($value, int $dec=0){
        $this->value = $value;
        $this->decimal = $dec;

        if(!$dec)
            $this->final = (int)$value;
        else
            $this->final = round(floatval($value), $dec);
    }

    public function __get($name){
        return $this->$name;
    }

    public function __toString(){
        return (string)$this->final;
    }

    public function format($dec=0, $dsep=',', $tsep='.'){
        return number_format($this->final, $dec, $dsep, $tsep);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return $this->final;
    }
}
