<?php
/**
 * Validator filters
 * @package lib-validator
 * @version 0.0.1
 */

namespace LibValidator\Filter;

class General
{
    static function array($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : (array)$value;
    }

    static function boolean($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : (bool)$value;
    }

    static function float($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : (float)$value;
    }

    static function integer($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : (int)$value;
    }

    static function lowercase($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : strtolower($value);
    }

    static function object($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : (object)$value;
    }

    static function round($value, $options, $object, $field, $filters){
        if(is_null($value))
            return $value;
        return is_int($options) ? round($value, $options) : round($value);
    }

    static function string($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : (string)$value;
    }

    static function ucwords($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : ucwords($value);
    }

    static function uppercase($value, $options, $object, $field, $filters){
        return is_null($value) ? $value : strtoupper($value);
    }
}