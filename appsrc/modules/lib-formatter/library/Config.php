<?php
/**
 * Config
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Library;

class Config
{

    static function reconfig(object &$configs, string $here) {
        $handlers = $configs->libFormatter->handlers;
        $new_handlers = (object)[];

        foreach($handlers as $name => $opts){
            $handler = explode('::', $opts->handler);
            $opts->handler = (object)[
                'class' => $handler[0],
                'method'=> $handler[1]
            ];
            $new_handlers->$name = $opts;
        }

        $configs->libFormatter->handlers = $new_handlers;
    }
}