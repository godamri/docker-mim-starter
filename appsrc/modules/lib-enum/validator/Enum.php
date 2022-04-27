<?php
/**
 */

namespace LibEnum\Validator;

class Enum{
    static function in($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        $enums = \Mim::$app->config->libEnum->enums->$options ?? null;
        if(!$enums)
            return ['22.0'];

        $enums = (array)$enums;

        if(!is_array($value)){
            if(array_key_exists($value, $enums))
                return null;
            return ['22.1'];
        }

        foreach($value as $val){
            if(!array_key_exists($val, $enums))
                return ['22.2'];
        }

        return null;
    }
}