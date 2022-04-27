<?php
/**
 * Middleware base
 * @package core
 * @version 0.0.1
 */

namespace Mim;

class Middleware
{
    public function __get(string $name){
        return \Mim::$app->$name;
    }
}