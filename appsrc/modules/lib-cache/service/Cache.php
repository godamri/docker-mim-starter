<?php

namespace LibCache\Service;

class Cache extends \Mim\Service
{
    private $handler;

    public function __construct(){
        $config = $this->config->libCache;
        $driver = $config->driver;
        $handler= $config->handlers->$driver;

        $this->handler = new $handler();
    }

    public function __call($name, $args=[]){
        return call_user_func_array([$this->handler, $name], $args);
    }
}