<?php
/**
 * Index
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

use LibModelMysql\Library\{
    IndexDescriptor,
    SchemaFiller
};

class Index
{

    static function test(string $model, array $indexes, array $fields): array{
        $table_indexes  = IndexDescriptor::describe($model, $fields);
        $schema_indexes = SchemaFiller::index($indexes, $fields);

        if(!$table_indexes){
            if($schema_indexes)
                return ['index_create'=>$schema_indexes];
            return [];
        }


        $result = [];

        // delete index
        foreach($table_indexes as $name => $index){
            if(!isset($schema_indexes[$name])){
                if(!isset($result['index_delete']))
                    $result['index_delete'] = [];
                $result['index_delete'][] = $index;
            }
        }

        // create and update
        foreach($schema_indexes as $name => $index){
            $table_index = $table_indexes[$name] ?? null;

            if(!isset($table_index)){
                if(!isset($result['index_create']))
                    $result['index_create'] = [];
                $result['index_create'][] = $index;
                continue;
            }

            $diff_found = false;

            // compare type
            if($table_index['type'] != $index['type'])
                $diff_found = true;

            // compare fields
            if(!$diff_found){
                $index_keys = array_keys($index['fields']);
                $table_index_keys = array_keys($table_index['fields']);
                if($index_keys !== $table_index_keys)
                    $diff_found = true;
            }

            // compare length
            if(!$diff_found){
                foreach($index['fields'] as $name => $prop){
                    $table_index_prop = $table_index['fields'][$name];
                    $idx_length = $prop['length'] ?? null;
                    $tbl_length = $table_index_prop['length'] ?? null;

                    if($idx_length != $tbl_length){
                        $diff_found = true;
                        break;
                    }
                }
            }

            if($diff_found){
                if(!isset($result['index_update']))
                    $result['index_update'] = [];
                $result['index_update'][] = $index;
            }
        }
        return $result;
    }
}