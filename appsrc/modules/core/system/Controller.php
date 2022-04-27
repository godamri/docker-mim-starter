<?php
/**
 * Base controller
 * @package core
 * @version 0.0.1
 */

namespace Mim;

class Controller
{
    public function __get(string $name){
        return \Mim::$app->$name;
    }    
}