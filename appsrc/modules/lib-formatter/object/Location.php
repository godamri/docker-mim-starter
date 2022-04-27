<?php
/**
 * Location object
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class Location implements \JsonSerializable
{
    private $lat;
    private $long;
    private $embed;
    private $value;

    public function __construct(string $place){
        $loc = explode(',', $place);
        $this->lat  = $loc[0];
        $this->long = $loc[1];
        $this->value = $place;
    }

    public function __get($name){
        if($name === 'embed' && !$this->embed)
            $this->embed = new LocationEmbed($this->lat, $this->long);
        return $this->$name ?? null;
    }

    public function __toString(){
        return $this->value;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return $this->__toString();
    }
}
