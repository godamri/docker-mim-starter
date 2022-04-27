<?php
/**
 * Query
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

use LibModelMysql\Library\{
    QueryIndex,
    QueryTable
};

class Query
{
    static function buildMutliple(string $model, array $data, array $diff): array{
        $result = [];

        // .table
        if(isset($diff['table_create']) && $diff['table_create'])
            $result[] = QueryTable::tableCreate($model, $diff['table_create']);

        // .fields
        if(isset($diff['field_delete']) && $diff['field_delete']){
            foreach($diff['field_delete'] as $field)
                $result[] = QueryTable::fieldDeleteSingle($model, $field);
        }

        if(isset($diff['field_create']) && $diff['field_create']){
            foreach($diff['field_create'] as $field)
                $result[] = QueryTable::fieldCreateSingle($model, $data['fields'], $field);
        }

        if(isset($diff['field_update']) && $diff['field_update']){
            foreach($diff['field_update'] as $field)
                $result[] = QueryTable::fieldUpdateSingle($model, $data['fields'], $field);
        }

        // .indexes
        if(isset($diff['index_delete']) && $diff['index_delete']){
            foreach($diff['index_delete'] as $index)
                $result[] = QueryIndex::indexDeleteSingle($model, $index);
        }

        if(isset($diff['index_create']) && $diff['index_create']){
            foreach($diff['index_create'] as $index)
                $result[] = QueryIndex::indexCreateSingle($model, $index);
        }

        if(isset($diff['index_update']) && $diff['index_update']){
            foreach($diff['index_update'] as $index){
                $result[] = QueryIndex::indexDeleteSingle($model, $index);
                $result[] = QueryIndex::indexCreateSingle($model, $index);
            }
        }

        if(isset($diff['data_create']) && $diff['data_create']){
            foreach($diff['data_create'] as $row)
                $result[] = QueryData::dataCreateSingle($model, $row);
        }

        return $result;
    }

    static function build(string $model, array $data, array $diff): string{
        $nl = PHP_EOL;
        $tx = '';

        // .table
        if(isset($diff['table_create']) && $diff['table_create'])
            $tx.= QueryTable::tableCreate($model, $diff['table_create']) . $nl;
        
        // .fields
        if(isset($diff['field_delete']) && $diff['field_delete'])
            $tx.= QueryTable::fieldDelete($model, $diff['field_delete']) . $nl;
        
        if(isset($diff['field_create']) && $diff['field_create'])
            $tx.= QueryTable::fieldCreate($model, $data['fields'], $diff['field_create']) . $nl;
        
        if(isset($diff['field_update']) && $diff['field_update'])
            $tx.= QueryTable::fieldUpdate($model, $data['fields'], $diff['field_update']) . $nl;

        // .indexes
        if(isset($diff['index_delete']))
            $tx.= QueryIndex::indexDelete($model, $diff['index_delete']) . $nl;

        if(isset($diff['index_create']))
            $tx.= QueryIndex::indexCreate($model, $diff['index_create']) . $nl;

        if(isset($diff['index_update']))
            $tx.= QueryIndex::indexUpdate($model, $diff['index_update']) . $nl;

        // .data
        if(isset($diff['data_create']))
            $tx.= QueryData::dataCreate($model, $diff['data_create']) . $nl;

        if($tx)
            $tx = '-- ' . $model . $nl . $tx . $nl;

        return $tx;
    }
}