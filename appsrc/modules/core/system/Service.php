<?php
/**
 * Service parent
 * @package core
 * @version 0.0.1
 */

namespace Mim;

class Service
{
    public function __get(string $name){
        return \Mim::$app->$name;
    }
}