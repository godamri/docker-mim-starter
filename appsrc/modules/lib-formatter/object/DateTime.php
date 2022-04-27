<?php
/**
 * Custom datetime
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class DateTime extends \DateTime implements \JsonSerializable
{
    private $timezone;
    private $time;
    private $value;

    public function __construct(string $time=null, \DateTimeZone $timezone=null){
        if(is_null($time))
            return;
        parent::__construct($time, $timezone);
        $this->value = $time;
        $this->time = $this->getTimestamp();
        $this->timezone = $this->getTimezone()->getName();
    }

    public function __get($name){
        return $this->$name ?? null;
    }

    public function __toString(){
        return $this->time ? $this->format('c') : '';
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return $this->time ? $this->__toString() : null;
    }
}
