<?php
/**
 * Model base
 * @package lib-model
 * @version 0.0.1
 */

namespace Mim;

class Model
{
    private static $models = [];

    private static function buildModel($model){
        $options = [
            'model' => $model,
            'table' => $model::$table,
            'chains' => $model::$chains,
            'q_field' => $model::$q,
            'connections' => ['write'=>null, 'read'=>null]
        ];

        $config = \Mim::$app->config->libModel;

        $conn = [
            'read'  => 'default',
            'write' => 'default'
        ];

        if(isset($config->model->$model))
            $conn = (array)$config->model->$model;

        if(!isset($config->connections))
            trigger_error('No DB connection found on app config');

        if(isset($config->alias)){
            foreach($conn as $name => $target){
                if(!isset($config->alias->$target))
                    continue;
                $conn[$name] = $config->alias->$target;
            }
        }

        if(!isset($config->connections->{$conn['read']}))
            trigger_error('DB Connection named `' . $conn['read'] . '` not found');
        if(!isset($config->connections->{$conn['write']}))
            trigger_error('DB Connection named `' . $conn['read'] . '` not found');

        $conn['read']   = $config->connections->{$conn['read']};
        $conn['write']  = $config->connections->{$conn['write']};

        $options['connections'] = $conn;

        $driver = $conn['read']->driver;

        if(!isset($config->drivers))
            trigger_error('No model driver installed, please install one');

        if(!isset($config->drivers->$driver))
            trigger_error('Model driver named `' . $driver . '` not registered');

        $handler = $config->drivers->$driver;
        
        self::$models[$model] = new $handler($options);
    }

    static function __callStatic($name, $args){
        $model = get_called_class();
        if(!isset(self::$models[$model]))
            self::buildModel($model);
        $model = self::$models[$model];

        return call_user_func_array([$model, $name], $args);
    }
}