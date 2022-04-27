<?php
/**
 * Schema
 * @package lib-model
 * @version 0.0.1
 */

namespace LibModel\Library;

use Mim\Library\Fs;
use StableSort\StableSort;

class Schema
{

    /**
     * @param array $t_includes List of table to include. Include all on falsy.
     * @param array $c_excludes List of connections name to exclude. Include all on falsy.
     * @return array
     */
    static function collectSchema(array $t_includes=[], array $c_excludes=[]): array {
        $result = [];

        $modules = Fs::scan(BASEPATH . '/modules');
        foreach($modules as $module){
            $migrate_files = [
                BASEPATH . '/modules/' . $module . '/migrate.php',
                BASEPATH . '/app/' . $module . '/migrate.php'
            ];
            foreach($migrate_files as $module_migrate_file){
                if(!is_file($module_migrate_file))
                    continue;
                $module_migrate = include $module_migrate_file;
                $result = array_replace_recursive($result, $module_migrate);
            }
        }

        // app migrate?
        $app_migrate_file = BASEPATH . '/etc/migrate.php';
        if(is_file($app_migrate_file)){
            $app_migrate = include $app_migrate_file;
            $result = array_replace_recursive($result, $app_migrate);
        }

        $filtered_result = [];

        // sort the fields
        foreach($result as $model => &$conf){
            if(!isset($conf['fields']))
                continue;
            foreach($conf['fields'] as $name => &$field)
                $field['name'] = $name;
            unset($field);

            StableSort::uasort($conf['fields'], function($a, $b){
                return ( $a['index'] ?? 100 ) - ( $b['index'] ?? 100 );
            });
        }
        unset($conf);

        if(!$t_includes && !$c_excludes)
            return $result;

        foreach($result as $model => $opts){
            if(!class_exists($model))
                continue;
            $table = $model::getTable();

            if($c_excludes){
                $conn = $model::getConnectionName('write');
                if(in_array($conn, $c_excludes))
                    continue;
            }

            if($t_includes && !in_array($table, $t_includes))
                continue;

            $filtered_result[$model] = $opts;
        }

        return $filtered_result;
    }

    static function getMigrator(array $models): array{
        $result = [];
        $migrators = \Mim::$app->config->libModel->migrators;
        foreach($models as $model => $data){
            if(!class_exists($model))
                continue;
            $driver = $model::getDriver();
            if(!isset($migrators->$driver))
                Bash::error('Migrator for driver `' . $driver . '` not found');
            $migrate_class = $migrators->$driver;
            $result[$model] = new $migrate_class($model, $data);
        }

        return $result;
    }
}