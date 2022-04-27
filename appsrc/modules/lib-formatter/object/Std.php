<?php
/**
 * Object std class
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class Std implements \JsonSerializable
{
    public $id;

    public function __construct($id){
        $this->id = $id;
    }

    public function __toString(){
        return (string)$this->id;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return (object)['id'=>$this->id];
    }
}
