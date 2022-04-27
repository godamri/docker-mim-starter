<?php
/**
 * Object type text
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Object;

class Text implements \JsonSerializable
{
    private $value;
    private $clean;
    private $safe;

    public function __construct(string $text=null){
        $this->value = $text ?? '';
    }

    public function __get($name){
        if($name === 'clean')
            return $this->getClean();
        if($name === 'safe')
            return $this->getSafe();
        return $this->$name ?? null;
    }

    public function __toString(){
        return $this->value;
    }

    public function chars(int $len): string{
        return substr($this->value, 0, $len);
    }

    public function getClean(){
        if(is_null($this->clean)){
            $text = preg_replace('!<[^>]+>!', '', $this->value);
            $this->clean = new Text(preg_replace('![^a-zA-Z0-9 ]!', '', $text));
        }
        return $this->clean;
    }

    public function getSafe(){
        if(is_null($this->safe))
            $this->safe = new Text(hs($this->value));
        return $this->safe;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(){
        return $this->__toString();
    }

    public function words(int $len): string{
        $index = -1;
        for($i=0; $i<$len; $i++){
            $index = strpos($this->value, ' ', $index+1);
            if(false === $index){
                $index = strlen($this->value);
                break;
            }
        }
        return trim(substr($this->value, 0, $index));
    }
}
