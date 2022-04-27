<?php
/**
 * Filesystem 
 * @package core
 * @version 0.0.2
 */

namespace Mim\Library;

class Fs
{
    static function cleanUp(string $path): bool{
        if(!is_dir($path))
            return false;
        $files = self::scan($path);
        if($files)
            return true;
        $parent = dirname($path);
        self::rmdir($path);
        return self::cleanUp($parent);
    }
    
    static function copy(string $source, string $target): bool{
        $target_dir = dirname($target);
        self::mkdir($target_dir);
        return copy($source, $target);
    }
    
    static function mkdir(string $path): bool {
        if(is_dir($path))
            return true;
        return mkdir($path, 0777, true);
    }
    
    static function rmdir(string $path): bool{
        $files = Fs::scan($path);
        if($files){
            foreach($files as $file){
                $file_abs = $path . '/' . $file;
                if(is_dir($file_abs))
                    self::rmdir($file_abs);
                else
                    unlink($file_abs);
            }
        }
        
        return rmdir($path);
    }
    
    static function scan(string $path): ?array {
        if(!is_dir($path))
            return null;
        return array_values(array_diff(scandir($path), ['.', '..']));
    }
    
    static function write(string $path, string $text, bool $append=false): bool {
        $fname = basename($path);
        $dname = dirname($path);
        if(!Fs::mkdir($dname))
            return false;
        if(false === ($f = fopen($path, ($append?'a':'w'))))
            return false;
        fwrite($f, $text);
        return fclose($f);
    }
}