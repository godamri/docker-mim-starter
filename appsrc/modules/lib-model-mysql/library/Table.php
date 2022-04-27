<?php
/**
 * Table
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

use LibModelMysql\Library\{
    TableDescriptor,
    SchemaFiller
};
use Cli\Library\Bash;

class Table
{
    static function test(string $model, array $fields): ?array{
        $table_fields  = TableDescriptor::describe($model);
        if(!$table_fields)
            return ['table_create'=>SchemaFiller::table($fields)];

        $table_fields_indexed = array_values($table_fields);
        $table_fields_index_by_name = [];
        foreach($table_fields_indexed as $index => $field)
            $table_fields_index_by_name[$field['name']] = $index;

        $schema_fields = SchemaFiller::table($fields);
        $schema_fields_indexed = array_values($schema_fields);

        $result = [];

        // delete field
        foreach($table_fields as $name => $field){
            if(isset($schema_fields[$name]))
                continue;

            if(!isset($result['field_delete']))
                $result['field_delete'] = [];

            $result['field_delete'][] = $field;
            $index = $table_fields_index_by_name[$name];
            unset($table_fields_indexed[$index]);
        }

        $table_fields_indexed = array_values($table_fields_indexed);

        // create and update
        foreach($schema_fields_indexed as $index => $field){
            // create
            if(!isset($table_fields[$field['name']])){
                if(!isset($result['field_create']))
                    $result['field_create'] = [];

                $result['field_create'][] = $field;
                array_splice($table_fields_indexed, $index, 0, [$field]);
                continue;
            }

            // column diff
            $table_field = $table_fields[$field['name']];
            $table_field_flat = array_flatten($table_field);

            $schema_flat = array_flatten($field);

            foreach($table_field_flat as $name => $val){
                $schem_val = $schema_flat[$name] ?? null;
                if($schem_val != $val){
                    if(!isset($result['field_update']))
                        $result['field_update'] = [];

                    $result['field_update'][] = $field;
                    continue 2;
                }
            }

            // column index diff
            $table_field_by_index = $table_fields_indexed[$index] ?? null;
            if(!$table_field_by_index)
                continue;

            if($table_field_by_index['name'] != $field['name']){
                if(!isset($result['field_update']))
                    $result['field_update'] = [];
                $result['field_update'][] = $field;
                continue;
            }
        }

        if(!$result)
            return null;
        return $result;
    }
}
