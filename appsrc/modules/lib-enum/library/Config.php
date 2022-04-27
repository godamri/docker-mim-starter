<?php
/**
 * Config
 * @package lib-enum
 * @version 0.0.1
 */

namespace LibEnum\Library;

class Config
{

    static function reconfig(object &$configs, string $here) {
        if(!isset($configs->libEnum) || !isset($configs->libEnum->enums))
            return;

        foreach($configs->libEnum->enums as &$val)
            $val = (array)$val;
        unset($val);
    }
}