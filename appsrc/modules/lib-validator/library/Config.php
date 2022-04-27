<?php
/**
 * Config
 * @package lib-validator
 * @version 0.0.1
 */

namespace LibValidator\Library;

class Config
{

    static function reconfig(object &$config, string $base): void{
        $parses = ['validators', 'filters'];

        foreach($parses as $name){
            $ovalue = $config->libValidator->$name;
            $rvalue = (object)[];

            foreach($ovalue as $oname => $handler){
                $hdrs = explode('::', $handler);
                $class = $hdrs[0];
                $method= $hdrs[1];

                $rvalue->$oname = (object)[
                    'class' => $class,
                    'method'=> $method
                ];
            }

            $config->libValidator->$name = $rvalue;
        }
    }
}