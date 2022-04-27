<?php
/**
 * Config service
 * @package core
 * @version 0.0.1
 */

namespace Mim\Service;

class Config extends \Mim\Service{
    
    public function __get(string $name){
        return \Mim::$_config->$name ?? null;
    }
}