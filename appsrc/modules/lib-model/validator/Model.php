<?php
/**
 * Custom validator for lib-validator
 * @package lib-model
 * @version 0.0.1
 */

namespace LibModel\Validator;

class Model
{
    static function unique($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;

        $model  = $options->model;
        $mfield = $options->field;
        $mself  = $options->self ?? null;
        $mwhere = $options->where ?? null;

        $cond = [$mfield => $value];
        if($mwhere)
            $cond = array_replace((array)$mwhere, $cond);

        $row = $model::getOne($cond);
        if(!$row)
            return null;

        if(!$mself)
            return ['14.0'];

        $obj = \Mim::$app;
        $mself_serv = explode('.', $mself->service);
        foreach($mself_serv as $prop){
            $obj = $obj->$prop ?? null;
            if(is_null($obj))
                break;
        }

        $row_val = $row->{$mself->field};

        if($row_val == $obj)
            return null;
        return ['14.0'];
    }

    static function exists($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;
        if(!$value)
            return null;

        $model  = $options->model;
        $mfield = $options->field;
        $mwhere = $options->where ?? null;

        $cond = [$mfield => $value];
        if($mwhere)
            $cond = array_replace((array)$mwhere, $cond);

        $row = $model::getOne($cond);
        if($row)
            return null;
        return ['19.0'];
    }

    static function existsList($value, $options, $object, $field, $rules): ?array{
        if(is_null($value))
            return null;
        if(!$value)
            return null;

        $value = (array)$value;

        $model  = $options->model;
        $mfield = $options->field;
        $mwhere = $options->where ?? null;

        $cond = [$mfield => $value];
        if($mwhere)
            $cond = array_replace((array)$mwhere, $cond);

        $rows = $model::get($cond);
        if(!$rows)
            return ['20.0'];

        $values = array_column($rows, $mfield);
        foreach($value as $val){
            if(!in_array($val, $values))
                return ['20.0'];
        }

        return null;
    }
}