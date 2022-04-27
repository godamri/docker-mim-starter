<?php
/**
 * IndexDescriptor
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

class IndexDescriptor
{

    static function describe(string $model, array $fields): ?array {
        $sql = $model::putFrom('SHOW INDEXES (:from)');
        $indexes = $model::query($sql, 'write');
        if(!$indexes)
            return null;

        $result = [];

        foreach($indexes as $index){
            $name = $index->Key_name;

            // skip primary name
            if($name === 'PRIMARY')
                continue;

            // is unique/primary key
            if(isset($fields[$name])){
                $is_unique  = $fields[$name]['attrs']['unique'] ?? false;
                $is_primary = $fields[$name]['attrs']['primary_key'] ?? false;
                if($is_unique || $is_primary)
                    continue;
            }

            if(!isset($result[$name])){
                $result[$name] = [
                    'name' => $name,
                    'type' => $index->Index_type,
                    'fields' => []
                ];
            }

            $idx = [];
            if($index->Sub_part)
                $idx['length'] = $index->Sub_part;
            $field = $index->Column_name;

            $result[$name]['fields'][$field] = $idx;
        }

        return $result;
    }
}