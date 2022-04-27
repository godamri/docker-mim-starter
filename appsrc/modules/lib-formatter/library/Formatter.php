<?php
/**
 * Formatter
 * @package lib-formatter
 * @version 0.7.0
 */

namespace LibFormatter\Library;

class Formatter implements \LibFormatter\Iface\Formatter
{
    protected static $authorized = null;

    protected static function isLogin ()
    {
        if(!is_null(self::$authorized))
            return self::$authorized;

        self::$authorized = \Mim::$app->user->isLogin();

        return self::$authorized;
    }

    public static function format (
        string $format,
        object $object,
        array $options=[]
    ): ?object {
        $result = self::formatMany($format, [$object], $options);
        if(!$result)
            return null;
        return $result[0];
    }

    static function formatApply(
        object $formats,
        array $objects,
        array $options = [],
        string $askey = null
    ): ?array {

        // implement format for rest property
        if(isset($formats->{'@rest'})){
            foreach($objects as $object){
                foreach($object as $prop => $val){
                    if(!isset($formats->$prop))
                        $formats->$prop = $formats->{'@rest'};
                }
                break;
            }
            unset($formats->{'@rest'});
        }

        // apply default value for falsy value
        // apply unauthorized for non logged in user
        foreach($formats as $field => $opts) {
            $def = $opts->{'@default'} ?? NULL;
            $una = $opts->{'@unauthorized'} ?? NULL;

            if(!$def && !$una)
                continue;

            foreach($objects as &$object) {
                if($def && (!isset($object->$field) || !$object->$field))
                    $object->$field = $def;
                if($una && !self::isLogin())
                    $object->$field = $una;
            }
            unset($object);
        }

        $handlers = \Mim::$app->config->libFormatter->handlers;
        $collective_data = [];

        // 1. Group properties by collectivity type.
        //  0 => non collective
        //  1 => collective
        $collectives = [[],[]];
        foreach($formats as $field => $opts){
            $type    = $opts->type;
            if(!isset($handlers->$type))
                trigger_error('Handler for type `' . $type . '` not found');

            $handler = $handlers->$type;
            $index   = $handler->collective ? 1 : 0;
            $collectives[$index][] = $field;
        }

        // 2. Collect objects properties which is collective type.
        if($collectives[1]){
            $collect_prop = [];
            foreach($collectives[1] as $field){
                $type    = $formats->$field->type;
                $handler = $handlers->$type;
                $prop    = $handler->field ?? $field;
                $collect_prop[$field] = $prop;
            }

            foreach($objects as $object){
                foreach($collect_prop as $field => $prop){
                    if(isset($object->$prop))
                        $collective_data[$field][] = $object->$prop;
                }
                if(isset($collective_data[$field]))
                    $collective_data[$field] = array_unique($collective_data[$field]);
            }


            // 3. Process collective properties.
            foreach($collective_data as $field => $values){
                $type = $formats->$field->type;
                $handler = $handlers->$type->handler;

                $class = $handler->class;
                $method= $handler->method;

                $format = $formats->$field;

                $fopts   = null;

                if(array_key_exists($field, $options))
                    $fopts = $options[$field];
                elseif(in_array($field, $options))
                    $fopts = [];

                $collective_data[$field] = $class::$method($values, $field, $objects, $format, $fopts);
            }
        }

        // 4. Process non collective, and put collective value
        foreach($formats as $field => $opts){
            $type       = $opts->type;
            $handler    = $handlers->$type;
            $collective = $handler->collective;

            // for non collective data
            $fopts   = null;
            if(in_array($field, $options))
                $fopts = true;
            elseif(isset($options[$field]))
                $fopts = $options[$field];

            // for collective data
            $cprop = $handler->field ?? $field;
            if(is_string($collective))
                $cprop = $collective;

            foreach($objects as &$object){
                if(!$collective){
                    $value = $object->$field ?? null;
                    $res = self::typeApply($type, $value, $field, $object, $opts, $fopts);
                    if(!is_null($res))
                        $object->$field = $res;
                // put collective data
                }else{
                    $value = $object->$cprop ?? null;

                    if(is_object($value))
                        $value = (string)$value;

                    if($cprop === '_MD5_')
                        $value = md5($object->$field);

                    if(isset($collective_data[$field][$value]))
                        $object->$field = $collective_data[$field][$value];
                    else
                        $object->$field = null;
                }

                if(isset($opts->{'@rename'})){
                    $object->{$opts->{'@rename'}} = $object->$field;
                    unset($object->$field);
                }
            }
            unset($object);
        }

        // process askey
        if(!$askey)
            return $objects;
        return prop_as_key($objects, $askey);
    }

    static function formatMany(string $format, array $objects, array $options=[], string $askey=null): ?array{
        $formats = \Mim::$app->config->libFormatter->formats->$format ?? null;
        if(!$formats){
            trigger_error('Format named `' . $format . '` not exists');
            return null;
        }

        return self::formatApply($formats, $objects, $options, $askey);
    }

    static function typeApply(string $type, $value, string $field, object $object, $format, $options){
        $handlers = \Mim::$app->config->libFormatter->handlers;
        if(!isset($handlers->$type)){
            trigger_error('Formatter type `' . $type . '` not exists');
            return null;
        }

        $handler = $handlers->$type->handler;
        $class   = $handler->class;
        $method  = $handler->method;

        return $class::$method($value, $field, $object, $format, $options);
    }
}
