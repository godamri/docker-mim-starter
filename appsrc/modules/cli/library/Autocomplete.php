<?php
/**
 * autocomplete provider
 * @package cli
 * @version 0.0.7
 */

namespace Cli\Library;

class Autocomplete extends \Cli\Autocomplete
{
    static function files(): string{
        return '2';
    }
    
    static function none(): string{
        return '1';
    }

    static function primary($args): string{
        $farg = $args[0] ?? '-';

        $gates  = include BASEPATH . '/etc/cache/gates.php';
        $routes = include BASEPATH . '/etc/cache/routes.php';

        $result = [];

        foreach($gates as $gate){
            if($gate->host->value !== 'CLI')
                continue;

            foreach($routes->{$gate->name} as $route){
                $bpath = explode(' ', trim($route->path->value))[0];
                if($bpath === 'autocomplete')
                    continue;

                if(!in_array($bpath, $result))
                    $result[] = $bpath;
            }
        }
        
        if($farg === '-')
            return trim(implode(' ', $result));

        return parent::lastArg($farg, $result);
    }
}