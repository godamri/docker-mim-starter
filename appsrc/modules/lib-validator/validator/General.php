<?php
/**
 * General validator
 * @package lib-validator
 * @version 1.3.3
 */

namespace LibValidator\Validator;

class General
{
    static function array($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if(!is_array($value))
            return ['1.0'];
        if($options === true)
            return null;

        $indexed = is_indexed_array($value);

        if($options === 'indexed' && !$indexed)
            return ['1.1'];
        if($options === 'assoc' && $indexed)
            return ['1.2'];
        return null;
    }

    static function callback($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        $handler = explode('::', $options);
        $class = $handler[0];
        $method= $handler[1];

        return $class::$method($value, $options, $object, $field, $rules);
    }

    static function config($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        if(isset($options->prop) && $options->prop){
            $cval = get_prop_value(\Mim::$app->config, $options->prop);
            if(!property_exists($cval, $value))
                return ['25.0'];
        }

        if(isset($options->in)){
            $cval = get_prop_value(\Mim::$app->config, $options->in);
            if(!in_array($value, $cval))
                return ['25.1'];
        }

        if(isset($options->is)){
            $cval = get_prop_value(\Mim::$app->config, $options->is);
            if($cval !== $value)
                return ['25.2'];
        }

        return null;
    }

    static function date($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        $date = date_create_from_format($options->format, $value);
        if(false === $date)
            return ['2.0'];
        $value_format = date_format($date, $options->format);
        if($value_format != $value)
            return ['2.1'];

        $value_time = date_create_from_format($options->format, $value_format);
        $value_time = $value_time->getTimestamp();

        $min = null;
        if(isset($options->min_field))
            $min = strtotime($object->{$options->min_field} ?? 'now');
        if(isset($options->min))
            $min = $min ? strtotime($options->min, $min) : strtotime($options->min);
        if($min){
            $min_time = date_create_from_format($options->format, date($options->format, $min));
            $min_time = $min_time->getTimestamp();
            if($min_time > $value_time)
                return ['2.2'];
        }

        $max = null;
        if(isset($options->max_field))
            $max = strtotime($object->{$options->max_field} ?? 'now');
        if(isset($options->max))
            $max = $max ? strtotime($options->max, $max) : strtotime($options->max);
        if($max){
            $max_time = date_create_from_format($options->format, date($options->format, $max));
            $max_time = $max_time->getTimestamp();
            if($max_time < $value_time)
                return ['2.3'];
        }

        return null;
    }

    static function email($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        $email = filter_var($value, FILTER_VALIDATE_EMAIL);
        if(false === $email)
            return ['3.0'];
        return null;
    }

    static function empty($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        if($options && $value)
            return ['21.1'];
        elseif(!$options && !$value)
            return ['21.0'];

        return null;
    }

    static function equalsTo($value, $options, $object, $field, $rules): ?array{
        $reff_field = $rules->equals_to;
        $reff_value = $object->{$reff_field};

        if($value !== $reff_value)
            return ['26.1'];
        return null;
    }

    static function file($value, $options, $object, $field, $rules): ?array{
        $file = $_FILES[$field] ?? null;
        if(!$file)
            return null;

        if($value !== $file)
            return ['28.0'];
        return null;
    }

    static function in($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if(!in_array($value, $options))
            return ['4.0'];
        return null;
    }

    static function ip($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if($options === true){
            if(false !== filter_var($value, FILTER_VALIDATE_IP))
                return null;
            return ['5.0'];
        }

        if($options == '4'){
            if(false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                return null;
            return ['5.1'];
        }

        if($options == '6'){
            if(false !== filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
                return null;
            return ['5.2'];
        }

        return null;
    }

    static function json($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        $tmp = json_decode($value);
        if(json_last_error() === JSON_ERROR_NONE)
            return null;
        return ['23.1'];
    }

    static function length($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        if(is_string($value))
            $len = strlen($value);
        elseif(is_array($value))
            $len = count($value);

        if(isset($options->min) && $len < $options->min)
            return ['6.0'];

        if(isset($options->max) && $len > $options->max)
            return ['6.1'];

        return null;
    }

    static function notin($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if(in_array($value, $options))
            return ['7.0'];
        return null;
    }

    static function numeric($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        if(!is_numeric($value))
            return ['8.0'];

        if(is_object($options)){
            if(isset($options->min) && $value < $options->min)
                return ['8.1'];


            if(isset($options->max) && $value > $options->max)
                return ['8.2'];

            if(isset($options->decimal)){
                $point = preg_replace('!^0\.!', '', (string)abs(round($value) - $value));
                if(strlen($point) != $options->decimal)
                    return ['8.3'];
            }
        }

        return null;
    }

    static function object($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        if(!is_object($value))
            return ['9.0'];
        return null;
    }

    static function regex($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if(!preg_match($options, $value))
            return ['10.0'];
        return null;
    }

    static function required($value, $options, $object, $field, $rules): ?array{
        if($options === true && is_null($value))
            return ['11.0'];
        return null;
    }

    static function requiredOn($value, $options, $object, $field, $rules): ?array{
        if(!is_null($value))
            return null;

        foreach($options as $ofield => $cond){
            $operator   = $cond->operator;
            $expect_val = $cond->expected;
            $other_val  = get_prop_value($object, $ofield) ?? NULL;

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

            if($match)
                return ['11.0'];
        }

        return null;
    }

    static function text($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if(!is_string($value))
            return ['12.0'];

        if($options === 'slug' && !preg_match('!^[a-z0-9-_]+$!', $value))
            return ['12.1'];

        if($options === 'alnumdash' && !preg_match('!^[a-zA-Z0-9-]+$!', $value))
            return ['12.2'];

        if($options === 'alpha' && !preg_match('!^[a-zA-Z]+$!', $value))
            return ['12.3'];

        if($options === 'alnum' && !preg_match('!^[a-zA-Z0-9]+$!', $value))
            return ['12.4'];

        return null;
    }

    static function url($value, $options, $object, $field, $rules): ?array{
        if(!$value)
            return null;

        if(!filter_var($value, FILTER_VALIDATE_URL))
            return ['13.0'];
        if(!is_object($options))
            return null;

        if(isset($options->path) && !filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED))
            return ['13.1'];

        if(isset($options->query)){
            if(!filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED))
                return ['13.2'];

            if(is_string($options->query))
                $options->query = (array)$options->query;

            if(is_array($options->query)){
                $query = parse_url($value, PHP_URL_QUERY);
                if(!$query)
                    return ['13.2'];

                parse_str($query, $qry);

                foreach($options->query as $val){
                    if(!isset($qry[$val]))
                        return ['13.3'];
                }
            }
        }

        return null;
    }
}
