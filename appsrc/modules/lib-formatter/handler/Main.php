<?php
/**
 * Main formatter handler
 * @package lib-formatter
 * @version 0.5.1
 */

namespace LibFormatter\Handler;

use LibFormatter\Library\Formatter;
use LibFormatter\Object\{
    DateTime,
    Embed,
    Interval,
    Location,
    Number,
    Text
};

class Main
{

    static function boolean($value, string $field, object $object, object $format, $options){
        return (bool)$value;
    }

    static function clone($value, string $field, object $object, object $format, $options){
        if(isset($format->source)){
            $result = get_prop_value($object, $format->source->field);
            if(isset($format->source->type))
                return Formatter::typeApply($format->source->type, $result, $field, $object, $format, $options);
            return $result;
        }elseif(isset($format->sources)){
            $result = (object)[];
            foreach($format->sources as $prop => $opt){
                $val = get_prop_value($object, $opt->field);
                if(isset($opt->type))
                    $val = Formatter::typeApply($opt->type, $val, $field, $object, $format, $options);
                $result->$prop = $val;
            }

            return $result;
        }
    }

    static function custom($value, string $field, object &$object, object $format, $options){
        $handler = explode('::', $format->handler);
        $class   = $handler[0];
        $method  = $handler[1];

        return $class::$method($value, $field, $object, $format, $options);
    }

    static function date($value, string $field, object $object, object $format, $options){
        if(isset($format->timezone))
            $value = new DateTime($value, new \DateTimeZone($format->timezone));
        else
            $value = new DateTime($value);
        return $value;
    }

    static function delete($value, string $field, object &$object, object $format, $options){
        if(property_exists($object, $field))
            unset($object->$field);
    }

    static function embed($value, string $field, object $object, object $format, $options){
        return new Embed($value);
    }

    static function interval($value, string $field, object $object, object $format, $options){
        return new Interval($value);
    }

    static function location($value, string $field, object $object, object $format, $options){
        if(!$value)
            return null;
        return new Location($value);
    }

    static function multipleText($value, string $field, object $object, object $format, $options){
        $sep = $format->separator ?? PHP_EOL;
        $vals = explode($sep, $value);
        $result = [];
        foreach($vals as $val)
            $result[] = new Text(trim($val));
        return $result;
    }

    static function number($value, string $field, object $object, object $format, $options){
        $dec = $format->decimal ?? 0;
        return new Number($value, $dec);
    }

    static function text($value, string $field, object $object, object $format, $options){
        return new Text($value);
    }

    static function json($value, string $field, object $object, object $format, $options){
        $value = json_decode($value);
        if(!isset($format->format))
            return $value;

        if(!is_array($options))
            $options = [];

        if(is_array($value))
            return Formatter::formatMany($format->format, $value, $options);
        return Formatter::format($format->format, $value, $options);
    }

    static function join($value, string $field, object $object, object $format, $options){
        $fields = $format->fields;
        $separator = $format->separator ?? '';
        $result = [];

        foreach($fields as $fld){
            if(substr($fld,0,1) === '$')
                $result[] = (string)get_prop_value($object, substr($fld,1));
            else
                $result[] = $fld;
        }

        return implode($separator, $result);
    }

    static function rename($value, string $field, object &$object, object $format, $options){
        $to = $format->to;
        $object->$to = $value;
        unset($object->$field);
    }

    static function router($value, string $field, object $object, object $format, $options){
        $router = $format->router;
        $params = [];

        if(isset($router->params)){
            foreach($router->params as $key => $source){
                if(substr($source, 0, 1) === '$'){
                    $params[$key] = get_prop_value($object, substr($source,1));
                }else{
                    $params[$key] = $source;
                }
            }
        }

        return \Mim::$app->router->to($router->name, $params);
    }

    static function switch($value, string $field, object $object, object $format, $options){
        $cases = $format->case;

        $result = null;

        foreach($cases as $case){
            $other_field= $case->field;
            $other_val  = null;
            if(substr($other_field, 0, 1) === '$'){
                $other_val = get_prop_value($object, substr($other_field,1));
            }else{
                $other_val = $object->{$other_field} ?? null;
            }

            $expect_val = $case->expected;
            $operator   = $case->operator;

            $match = false;

            switch($operator){
                case '=':
                    $match = $other_val == $expect_val;
                    break;
                case '!=':
                    $match = $other_val != $expect_val;
                    break;
                case '>':
                    $match = $other_val > $expect_val;
                    break;
                case '<':
                    $match = $other_val < $expect_val;
                    break;
                case '>=':
                    $match = $other_val >= $expect_val;
                    break;
                case '<=':
                    $match = $other_val <= $expect_val;
                    break;
                case 'in':
                    $match = in_array($other_val, $expect_val);
                    break;
                case '!in':
                    $match = !in_array($other_val, $expect_val);
                    break;
            }

            if(!$match)
                continue;

            $result = $case->result;
            break;
        }

        if(!$result)
            return $value;

        $handler = \Mim::$app->config->libFormatter->handlers->{$result->type};

        // for non collective
        if(!$handler->collective)
            return Formatter::typeApply($result->type, $value, $field, $object, $result, $options);

        $handler = $handler->handler;

        // for collective
        $class  = $handler->class;
        $method = $handler->method;
        $format = $result;

        $values = $class::$method([$value], $field, [$object], $format, $options);
        return $values[$value] ?? $value;
    }
}
