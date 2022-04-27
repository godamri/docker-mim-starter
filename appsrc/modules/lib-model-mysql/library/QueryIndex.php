<?php
/**
 * QueryIndex
 * @package lib-model-mysql
 * @version 0.0.1
 */

namespace LibModelMysql\Library;

class QueryIndex
{

    static function indexCreateSingle(string $model, array $index): string{
        $sql = 'CREATE';
        if(in_array($index['type'], ['FULLTEXT', 'UNIQUE', 'SPATIAL']))
            $sql.= ' ' . $index['type'];
        $sql.= ' INDEX `' . $index['name'] . '` ON (:table)';

        $sql = $model::putTable($sql, ['table'=>$model::getTable()]);

        $flds = [];

        foreach($index['fields'] as $name => $opts){
            $fld = '`' . $name . '`';
            if(isset($opts['length']))
                $fld.= '(' . $opts['length'] . ')';
            $flds[] = $fld;
        }

        $sql.= ' (' . implode(',', $flds) . ')';

        if(in_array($index['type'], ['BTREE', 'HASH']))
            $sql.= ' USING ' . $index['type'];

        $sql.= ';';

        return $sql;
    }

    static function indexDeleteSingle(string $model, array $index): string{
        return $model::putTable('DROP INDEX `' . $index['name'] . '` ON (:table);', [
            'table' => $model::getTable()
        ]);
    }

    static function indexDelete(string $model, array $indexes): string {
        $res = [];

        foreach($indexes as $index)
            $res[] = self::indexDeleteSingle($model, $index);

        return implode(PHP_EOL, $res);
    }

    static function indexCreate(string $model, array $indexes): string {
        $res = [];

        foreach($indexes as $name => $index)
            $res[] = self::indexCreateSingle($model, $index);

        return implode(PHP_EOL, $res);
    }

    static function indexUpdate(string $model, array $indexes): string {
        $res = [];

        foreach($indexes as $index){
            $res[] = self::indexDeleteSingle($model, $index);
            $res[] = self::indexCreateSingle($model, $index);
        }

        return implode(PHP_EOL, $res);
    }
}