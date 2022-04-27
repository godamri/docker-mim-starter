<?php
/**
 * Config
 * @package core
 * @version 1.1.2
 */

namespace Core\Library;


class Config
{
    static function reconfig(object &$configs, string $here) {
        if(!isset($configs->service))
            return;

        foreach($configs->service as $name => $handler){
            if(false === strstr($name, '/'))
                continue;

            $names    = explode('/', $name);
            $new_name = end($names);

            if(isset($configs->service->$new_name))
                continue;
            
            $configs->service->$new_name = $handler;
        }
    }
}