<?php
/**
 * Autocomplete handler
 * @package cli
 * @version 0.0.7
 */

namespace Cli;

class Autocomplete
{
    static function lastArg(string $arg, array $result): string{
        if(in_array($arg, $result))
            return '1';

        $arglen = strlen($arg);
        $match_found = false;
        foreach($result as $res){
            if($arg === substr($res, 0, $arglen)){
                $match_found = true;
                break;
            }
        }

        return $match_found ? trim(implode(' ', $result)) : '1';
    }
}