<?php
/**
 * Application manager
 * @package core
 * @version 1.8.2
 */

class Mim {

    static $app;
    static $_config;
    static $_service;
    
    /* object area */
    public function __construct(){
        self::$_service = [];
    }
    
    public function __get(string $name): object{
        if(isset(self::$_service[$name]))
            return self::$_service[$name];
            
        $services = self::$_config->service;
        if(!property_exists($services, $name))
            trigger_error('Service named `' . $name . '` not registered');
        return self::$_service[$name] = new $services->$name;
    }
    
    public function next(): void {
        Mim::$app->req->next();
    }
    
    /* static area */
    
    static function init(): bool {
        if (self::_selfServer())
            return false;

        self::_env();
        self::$_config = require_once BASEPATH . '/etc/cache/config.php';
        self::_dynamicHost();
        self::_autoload();
        self::_env_config();
        self::_timezone();
        self::_error();
        self::$app = new Mim;

        // call the `core->ready` callback
        if(isset(self::$_config->callback)
            && isset(self::$_config->callback->core->ready)){
            
            $callbacks = self::$app->config->callback->core->ready;
            foreach($callbacks as $handler){
                $class = $handler->class;
                $method= $handler->method;

                $class::$method();
            }

        }

        self::$app->next();

        return true;
    }
    
    private static function _autoload(): void {
        // load all files
        foreach(self::$_config->autoload->files as $file => $cond)
            $cond && require_once BASEPATH . '/' . $file;
        
        // load required classes
        spl_autoload_register(function($class){
            $file = Mim::$_config->autoload->classes->$class ?? null;
            if($file)
                include BASEPATH . '/' . $file;
        });

        // load commposer autoload
        $composer_autoload = BASEPATH . '/vendor/autoload.php';
        if(is_file($composer_autoload))
            include $composer_autoload;
    }

    private static function _dynamicHost(): void
    {
        if (self::$_config->host == '*' && isset($_SERVER['HTTP_HOST'])) {
            self::$_config->host = $_SERVER['HTTP_HOST'];
        }

        if (!isset(self::$_config->gates)) {
            return;
        }

        foreach (self::$_config->gates as $gate => &$info) {
            if ($info->host->value === '*') {
                $info->host->value = 'HOST';
            }
            if ($info->asset->host === '*') {
                $info->asset->host = 'HOST';
            }
        }
        unset($info);
    }
    
    private static function _env(): void {
        $env = file_get_contents(BASEPATH . '/etc/.env');
        $env = $env;
        
        define('ENVIRONMENT', $env);
        
        error_reporting(-1);
        if($env == 'development')
            ini_set('display_errors', 1);
        else
            ini_set('display_errors', 0);
    }

    private static function _env_config(): void{
        if(!isset(self::$_config->envMap))
            return;
        $env_map = self::$_config->envMap;
        
        foreach($env_map as $key => $map){
            $val = getenv($key);
            if(!$val)
                continue;

            $maps  = explode('.', $map);
            $ptemp = &self::$_config;
            foreach($maps as $mp){
                if(is_array($ptemp)){
                    if(!isset($ptemp[$mp]))
                        $ptemp[$mp] = (object)[];
                    $ptemp = &$ptemp[$mp];
                }elseif(is_object($ptemp)){
                    if(!isset($ptemp->$mp))
                        $ptemp->$mp = (object)[];
                    $ptemp = &$ptemp->$mp;
                }
            }
            $ptemp = $val;
            unset($ptemp);
        }
    }
    
    private static function _error(){
        set_error_handler(function($no, $text, $file, $line){
            \Mim\Library\Logger::error($no, $text, $file, $line);
        });
        set_exception_handler(['Mim\Library\Logger', 'exceptioned']);
    }
    
    private static function _timezone(): void{
        date_default_timezone_set(self::$_config->timezone);
    }

    private static function _selfServer()
    {
        if (php_sapi_name() !== 'cli-server') {
            return false;
        }

        $uri = ltrim($_SERVER['REQUEST_URI'], '/');
        $file_abs = BASEPATH . '/' . $uri;
        $file_abs = preg_replace('!\?.+$!', '', $file_abs);

        // rules to back to index.php:
        // - target not found
        // - target is dir
        // - target file start with .
        // - target file is php or phtml

        if (!file_exists($file_abs)){
            return false;
        }

        if (is_dir($file_abs))
            return false;

        $file_name = basename($file_abs);
        if (substr($file_name, 0, 1) == '.')
            return false;

        $file_ext = explode('.', $file_name);
        $file_ext = end($file_ext);
        $php_exts = ['php', 'phtml'];

        if (in_array($file_ext, $php_exts))
            return false;

        return true;
    }
}
