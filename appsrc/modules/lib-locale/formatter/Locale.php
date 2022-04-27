<?php
/**
 * `locale` type format ( lib-formatter )
 * @package lib-locale
 * @version 0.0.2
 */

namespace LibLocale\Formatter;

class Locale
{
    static function translate($value, string $field, object $object, object $format, $options): string{
        if(!$value)
            return '';
        if(is_string($value))
            $value = json_decode($value);

        if(!is_object($value))
            return '';

        $loc_params = [];

        // value source
        $locale  = $value->locale;

        $loc_key  = $locale->key;
        $loc_pars = $locale->params ?? [];
        $loc_pars = (array)$loc_pars;
        $loc_params = array_replace($loc_params, $loc_pars);

        // formatter source
        if(isset($format->locale)){
            $for_pars = $format->locale->params ?? [];
            $for_pars = (array)$for_pars;
            $loc_params = array_replace($loc_params, $for_pars);
        }

        foreach($loc_params as $key => $val){
            if(substr($val, 0, 1) === '$'){
                $loc_params[$key] = (string)get_prop_value($object, substr($val,1));
            }else{
                $loc_params[$key] = $val;
            }
        }

        $result = lang($loc_key, $loc_params);
        return $result;
    }
}