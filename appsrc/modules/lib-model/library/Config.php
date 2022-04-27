<?php
/**
 * Config
 * @package lib-model
 * @version 0.0.1
 */

namespace LibModel\Library;

class Config
{

    private static function setModel(object $configs, object &$libc): void{
        $autoload_classes = $configs->autoload->classes;
        $used_model = (object)[];

        $default_cons = $libc->target;

        foreach($libc->model as $model => $cons){
            $models = [];
            if(false !== strstr($model, '*')){
                $regex = preg_quote($model);
                $regex = str_replace('\*', '.+', $regex);
                foreach($autoload_classes as $class => $conf){
                    if(preg_match('!^'.$regex.'$!', $class))
                        $models[] = $class;
                }
            }else{
                $models[] = $model;
            }

            $rule = clone $default_cons;
            if(is_string($cons))
                $rule = (object)['read'=>$cons, 'write'=>$cons];
            else{
                if(isset($cons->read))
                    $rule->read = $cons->read;
                if(isset($cons->write))
                    $rule->write = $cons->write;
            }


            foreach($models as $class){
                if(!isset($used_model->$class))
                    $used_model->$class = $rule;
            }
        }

        $libc->model = $used_model;
    }

    private static function setConnection(object $configs, object &$libc): void{
        $used_conns = (object)[];
        foreach($libc->connections as $name => $conf){
            $conf->name = $name;
            if(!is_array($conf->configs))
                $conf->configs = (array)$conf->configs;
            $used_conns->$name = $conf;
        }
        $libc->connections = $used_conns;
    }

    static function reconfig(object &$configs, string $here) {
        $libc = $configs->libModel;
        if(!is_object($libc))
            $libc = (object)$libc;
        if(!isset($libc->connections))
            $libc->connections = (object)[];
        if(!isset($libc->drivers))
            $libc->drivers = (object)[];
        if(!isset($libc->target)){
            $libc->target = (object)[
                'read' => 'default',
                'write' => 'default'
            ];
        }
        if(!isset($libc->model))
            $libc->model = (object)[];

        if(count((array)$libc->model))
            self::setModel($configs, $libc);
        if(count((array)$libc->connections))
            self::setConnection($configs, $libc);
        $configs->libModel = $libc;
    }
}