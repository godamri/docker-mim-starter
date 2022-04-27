<?php
/**
 * TableDescriptor
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

class TableDescriptor
{

    static function describe(string $model): ?array{
        $table = $model::getTable();
        $res = $model::query('SHOW CREATE TABLE `' . $table . '`;', 'write');
        if(!$res)
            return null;

        $result = [];

        $create_syntax = $res[0]->{'Create Table'};
        $create_syntax = explode(PHP_EOL, $create_syntax);
        array_shift($create_syntax);
        array_pop($create_syntax);

        array_walk($create_syntax, function(&$a){ $a = trim($a, ' ,'); });

        $primary_keys = [];
        $unique_keys = [];
        foreach($create_syntax as $fld){
            if(preg_match('!^UNIQUE KEY `[^`]+` \(`(?<field>[^`]+)`\)$!', $fld, $match)){
                $unique_keys[] = $match['field'];

            }elseif(preg_match('!^PRIMARY KEY \(([^\)]+)\)$!', $fld, $match)){
                $keys = explode(',', $match[1]);
                array_walk($keys, function(&$a){ $a = trim($a, '` '); });
                $primary_keys = $keys;

            }elseif(preg_match('!^`(?<name>[^`]+)` (?<type>[a-z]+)(\((?<length>[^)]+)\))?.+?$!', $fld, $match)){
                $name = $match['name'];
                $field = [
                    'name' => $name,
                    'type' => null,
                    'length' => null,
                    'options' => [],
                    'attrs' => [
                        'null' => true,
                        'unique' => false,
                        'unsigned' => false,
                        'default' => null,
                        'update' => null,
                        'primary_key' => false,
                        'auto_increment' => false
                    ]
                ];

                // type
                $type = strtoupper($match['type']);
                if($type === 'INT')
                    $type = 'INTEGER';
                $field['type'] = $type;

                // length
                if(isset($match['length'])){
                    $length = $match['length'];
                    if(in_array($type, ['SET', 'ENUM'])){
                        $options = explode(',', $length);
                        array_walk($options, function(&$a){ $a = trim($a, " '"); });
                        $field['options'] = $options;
                    }else{
                        if(!in_array($type, ['BIGINT','MEDIUMINT','INTEGER','SMALLINT','TINYINT']))
                            $field['length'] = $length;
                    }
                }

                // auto_increment
                if(false !== strstr($fld, 'AUTO_INCREMENT'))
                    $field['attrs']['auto_increment'] = true;

                // not null
                if(false !== strstr($fld, 'NOT NULL'))
                    $field['attrs']['null'] = false;

                // unsigned
                if(false !== strstr($fld, 'unsigned'))
                    $field['attrs']['unsigned'] = true;

                // default
                if(preg_match('!DEFAULT \'?(?<def>[^\' ]+)\'?!', $fld, $def)){
                    $def = $def['def'];
                    if($def === 'NULL')
                        $def = null;
                    if($def === 'current_timestamp()')
                        $def = 'CURRENT_TIMESTAMP';
                    $field['attrs']['default'] = $def;
                }

                // update
                if(preg_match('!ON UPDATE \'?(?<update>[^\' ]+)\'?!', $fld, $def)){
                    $def = $def['update'];
                    if($def === 'NULL')
                        $def = null;
                    if($def === 'current_timestamp()')
                        $def = 'CURRENT_TIMESTAMP';
                    $field['attrs']['update'] = $def;
                }


                $result[$name] = $field;
            }
        }

        foreach($primary_keys as $key)
            $result[$key]['attrs']['primary_key'] = true;

        foreach($unique_keys as $key)
            $result[$key]['attrs']['unique'] = true;
        
        return $result;
    }
}