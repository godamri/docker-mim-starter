<?php
/**
 * Lib Cache file driver
 * @package lib-cache
 * @version 0.0.1
 */

namespace LibCache\Driver;

use \Mim\Library\Fs;

class File implements \LibCache\Iface\Driver
{
    private $base;

    private function makePath(string $name): string{
        return $this->base . '/' . $name . '.php';
    }

    public function __construct(){
        $base = \Mim::$app->config->libCache->file->base;
        if(!$base)
            $base = BASEPATH . '/etc/cache/lib-cache';

        if(substr($base,0,1) !== '/')
            $base = realpath( BASEPATH . '/' . $base );

        $this->base = $base;
    }

    public function add(string $name, $value, int $expires): void{
        $nl = PHP_EOL;

        $expires_at = time() + $expires;

        $tx = '<?php' . $nl;
        $tx.= $nl;
        $tx.= 'if(time() > ' . $expires_at . '){' . $nl;
        $tx.= '    unlink(__FILE__);' . $nl;
        $tx.= '    return null;' . $nl;
        $tx.= '}else{' . $nl;
        $tx.= '    return ' . to_source($value) . ';';
        $tx.= '}';

        $cache_file = $this->makePath($name);
        Fs::write($cache_file, $tx);
    }
    
    public function exists(string $name): bool{
        $cache = $this->makePath($name);
        return is_file($cache);
    }

    public function get(string $name){
        $cache = $this->makePath($name);
        if(!is_file($cache))
            return null;
        return include $cache;
    }
    
    public function list(): array{
        $files = Fs::scan($this->base);
        $result = [];
        foreach($files as $file){
            if(substr($file, -4) != '.php')
                continue;
            $is_expired = include $this->base . '/' . $file;
            if(!$is_expired)
                continue;
            $result[] = substr($file, 0, -4);
        }
        return $result;
    }
    
    public function remove(string $name): void{
        $cache = $this->makePath($name);
        if(is_file($cache))
            unlink($cache);
    }

    public function truncate(): void{
        $caches = $this->list();
        foreach($caches as $cache)
            $this->remove($cache);
    }
}