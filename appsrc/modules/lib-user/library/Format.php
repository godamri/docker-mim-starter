<?php
/**
 * Format
 * @package lib-user
 * @version 0.4.1
 */

namespace LibUser\Library;

use LibFormatter\Library\Formatter;
use LibFormatter\Object\Std;

class Format
{
    private static function asId(array $values): array{
        $result = [];
        foreach($values as $val)
            $result[$val] = new Std($val);
        return $result;
    }

    static function user(array $values, string $field, array $objects, object $format, $options): array{
        if(is_null($options)){
            $values = self::asId($values);
            foreach($values as $index => $val){
                $val->id = Formatter::typeApply('number', $val->id, 'id', $val, (object)[], null);
                $values[$index] = $val;
            }

            return $values;
        }

        $where = [
            'id' => $values
        ];
        if(is_array($options) && isset($options['_where'])){
            $where = array_replace($where, $options['_where']);
            unset($options['_where']);
        }
        $rows = Fetcher::get($where);

        if(!$rows)
            return [];

        return Formatter::formatMany('user', $rows, $options, 'id');
    }
}
