<?php
/**
 * Validator
 * @package lib-validator
 * @version 0.0.1
 */

namespace LibValidator\Library;
use LibValidator\Validator\General as VGeneral;
use LibValidator\Filter\General as FGeneral;

class Validator
{

    private static $rules = [];
    private static $filters = [];
    private static $trans = [];

    private static function buildRules(): void{
        $conf = \Mim::$app->config->libValidator;
        self::$rules    = $conf->validators;
        self::$filters  = $conf->filters;
        self::$trans    = (array)$conf->errors;
    }

    private static function buildError(array $data): object{
        $valid = $data['valid'];
        $rules = $data['rules'];
        $rule  = $data['rule'];
        $value = $data['value'];
        $field = $data['field'];
        $validation = $data['validation'];
        $parent = $data['parent'];

        $code = $valid[0];

        $params = $valid[1] ?? [];
        $params['field'] = $field;
        $params[$rule]   = $validation->rules->$rule;
        $params['value'] = $value;

        foreach($validation as $key => $val){
            if(in_array($key, ['rules', 'filters', 'children']))
                continue;
            $params[$key] = $val;
        }
        
        $result = (object)[
            'field' => ($parent?$parent.'.':'') . $field,
            'code'  => $code,
            'text'  => ''
        ];

        $lang_key = self::$trans[$code] ?? '';

        // custom translation key
        if(isset($validation->message) && isset($validation->message->$rule))
            $lang_key = $validation->message->$rule;

        $text = lang($lang_key, $params);
        $result->text = $text;

        return $result;
    }

    static function validate(object $validations, object $object, string $parent=''): array {
        if(!self::$rules)
            self::buildRules();

        $result = [$object, null];

        $new_object = (object)[];
        $new_errors = [];

        foreach($validations as $fname => $validation){
            $rules    = $validation->rules ?? [];
            $filters  = $validation->filters ?? [];
            $children = $validation->children ?? null;
            $next_parent = ($parent?$parent.'.':'') . $fname;

            $undifined = !property_exists($object, $fname);
            $value     = !$undifined ? $object->$fname : null;

            $is_valid = true;

            foreach($rules as $rname => $ropt){
                if(!isset(self::$rules->$rname))
                    trigger_error('Validator rule named `' . $rname . '` not registered');
                $handler = self::$rules->$rname;
                $class   = $handler->class;
                $method  = $handler->method;

                $valid = $class::$method($value, $ropt, $object, $fname, $rules);
                if(is_null($valid))
                    continue;
                $is_valid = false;

                $new_errors[$fname] = self::buildError([
                    'valid'         => $valid,
                    'rules'         => $rules,
                    'rule'          => $rname,
                    'value'         => $value,
                    'field'         => $fname,
                    'parent'        => $parent,
                    'validation'    => $validation
                ]);
                break;
            }

            if($is_valid){
                foreach($filters as $name => $fopt){
                    $handler = self::$filters->$name;
                    $class   = $handler->class;
                    $method  = $handler->method;
                    $value   = $class::$method($value, $fopt, $object, $fname, $filters);
                }

                // apply children
                if($children && $value){
                    // indexed array
                    if(isset($children->{'*'})){
                        $cvalidation = $children->{'*'};

                        foreach($value as $idx => $vitem){
                            $vobj = (object)[$idx => $vitem];
                            $vval = (object)[$idx => $cvalidation];

                            list($vres, $verr) = self::validate($vval, $vobj, $next_parent);
                            $value[$idx] = $vres->$idx;
                            if($verr){
                                foreach($verr as $key => $val)
                                    $new_errors[$fname.'.'.$key] = $val;
                            }
                        }
                    }else{
                        // non indexed array
                        $is_array = is_array($value);
                        list($cres, $cerr) = self::validate($children, (object)$value, $next_parent);
                        $value = $cres;
                        if($is_array)
                            $value = (array)$value;
                        if($cerr){
                            foreach($cerr as $key => $val)
                                $new_errors[$fname.'.'.$key] = $val;
                        }
                    }
                }
            }

            if(!$undifined)
                $new_object->$fname = $value;
        }

        $result[0] = $new_object;
        $result[1] = $new_errors;

        return $result;
    }
}