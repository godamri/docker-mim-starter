<?php
/**
 * QueryTable
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

class QueryTable
{

    private static function genFieldCreate(string $model, array $field): string{
        $sql = $model::putTable('ALTER TABLE (:table) ADD COLUMN ', [
            'table' => $model::getTable()
        ]);
        $sql.= self::genFieldMeta($model, $field);

        return $sql;
    }

    private static function genFieldMeta(string $model, array $field, bool $pk=true): string{
        $sql = $model::putFieldPlain('(:field)', ['field'=>$field['name']]);

        $sql.= ' ' . $field['type'];
        if($field['length'])
            $sql.= '(' . $field['length'] . ')';
        if($field['options'])
            $sql.= '(\'' . implode("','", $field['options']) . '\')';
        if($field['attrs']['unsigned'])
            $sql.= ' UNSIGNED';
        if(in_array($field['type'], ['CHAR', 'ENUM', 'LONGTEXT', 'SET', 'TEXT', 'TINYTEXT', 'VARCHAR']))
            $sql.= ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

        if(!$field['attrs']['null'])
            $sql.= ' NOT NULL';

        if($field['attrs']['unique'])
            $sql.= ' UNIQUE';

        $default = $field['attrs']['default'];
        if(!is_null($default)){
            $sql.= ' DEFAULT ';

            if($default === 'CURRENT_TIMESTAMP')
                $sql.= 'CURRENT_TIMESTAMP';
            elseif(substr($default, -2) === '()')
                $sql.= $default;
            elseif($field['type'] === 'BOOLEAN')
                $sql.= $default ? 'TRUE' : 'FALSE';
            else
                $sql.= "'" . $default . "'";
        }

        $update = $field['attrs']['update'];
        if(!is_null($update)){
            $sql.= ' ON UPDATE ';

            if($update === 'CURRENT_TIMESTAMP')
                $sql.= 'CURRENT_TIMESTAMP';
            elseif(substr($update, -2) === '()')
                $sql.= $update;
            elseif($field['type'] === 'BOOLEAN')
                $sql.= $update ? 'TRUE' : 'FALSE';
            else
                $sql.= "'" . $update . "'";
        }

        if($field['attrs']['auto_increment'])
            $sql.= ' AUTO_INCREMENT';

        if($pk && $field['attrs']['primary_key'])
            $sql.= ' PRIMARY KEY';

        return $sql;
    }

    private static function genFieldUpdate(string $model, array $field): string{
        $sql = $model::putTable('ALTER TABLE (:table) MODIFY ', [
            'table' => $model::getTable()
        ]);
        $sql.= self::genFieldMeta($model, $field);

        return $sql;
    }

    static function fieldCreate(string $model, array $fields, array $diff): string {
        $res = [];

        foreach($diff as $field)
            $res[] = self::fieldCreateSingle($model, $fields, $field);

        return implode(PHP_EOL, $res);
    }

    static function fieldCreateSingle(string $model, array $fields, array $field): string {
        $tx = '';

        $prev_field = null;
        foreach($fields as $fld){
            if($fld['name'] === $field['name']){
                $sql = self::genFieldCreate($model, $field);
                if(!$prev_field)
                    $sql.= ' FIRST;';
                else
                    $sql.= ' AFTER `' . $prev_field['name'] . '`;';

                $tx.= $sql;
            }else{
                $prev_field = $fld;
            }
        }

        return $tx;
    }

    static function fieldUpdate(string $model, array $fields, array $diff): string {
        $res = [];

        foreach($diff as $field)
            $res[] = self::fieldUpdateSingle($model, $fields, $field);

        return implode(PHP_EOL, $res);
    }

    static function fieldUpdateSingle(string $model, array $fields, array $field): string {
        $sql = '';
        $prev_field = null;
        foreach($fields as $fld){
            if($fld['name'] === $field['name']){
                $sql = self::genFieldUpdate($model, $field);
                if(!$prev_field)
                    $sql.= ' FIRST;';
                else
                    $sql.= ' AFTER `' . $prev_field['name'] . '`;';
            }else{
                $prev_field = $fld;
            }
        }

        return $sql;
    }

    static function fieldDelete(string $model, array $fields): string{
        $res = [];

        foreach($fields as $field)
            $res[] = self::fieldDeleteSingle($model, $field);

        return implode(PHP_EOL, $res);
    }

    static function fieldDeleteSingle(string $model, array $field): string{
        $sql = $model::putTable('ALTER TABLE (:table) DROP COLUMN (:field);', [
            'table' => $model::getTable()
        ]);

        $sql = $model::putField($sql, [
            'field' => $field['name']
        ]);

        return $sql;
    }

    static function tableCreate(string $model, array $fields): string{
        $nl = PHP_EOL;
        $tx = $model::putTable('CREATE TABLE IF NOT EXISTS (:table) ', [
            'table' => $model::getTable()
        ]);

        $flds = [];
        $pks = [];
        foreach($fields as $field){
            $flds[] = self::genFieldMeta($model, $field, false);
            if($field['attrs']['primary_key'])
                $pks[] = $field['name'];
        }

        if($pks)
            $flds[] = 'PRIMARY KEY (`' . implode('`,`', $pks) . '`)';

        $sp = $nl . '    ';

        $tx.= '(' . $sp . implode(', ' . $sp, $flds) . $nl . ')';
        $tx.= ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

        return $tx;
    }
}