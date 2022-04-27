<?php
/**
 * Config
 * @package lib-locale
 * @version 0.0.2
 */

namespace LibLocale\Library;

use Mim\Library\Fs;

class Config
{

    private static function generateLocale(string $locale_dir, string $base=''): array{
        $result = [];

        $files = Fs::scan($locale_dir);
        foreach($files as $file){
            if($file === '.gitkeep')
                continue;

            $file_abs = $locale_dir . '/' . $file;

            if(is_file($file_abs)){
                if(substr($file, -4) !== '.php')
                    continue;
                $file_base = basename($file, '.php');

                $file_langs = include $file_abs;
                foreach($file_langs as $key => $value){
                    $gen_key_base = $base . $file_base . '.' . $key;

                    $result[$gen_key_base] = $value;
                    if($file_base === 'main'){
                        $main_key_base = $base . $key;
                        $result[$main_key_base] = $value;
                    }
                }
            }elseif(is_dir($file_abs)){
                $res = self::generateLocale($file_abs, $base . $file . '.');
                $result = array_merge($result, $res);
            }
        }

        return $result;
    }

    static function reconfig(object &$configs, string $here): void{
        $source_locale_dir = $here . '/etc/locale';
        $target_locale_dir = $here . '/etc/cache/locale';
        $nl = PHP_EOL;

        $locales = Fs::scan($source_locale_dir);
        foreach($locales as $locale){
            $locale_abs = $source_locale_dir . '/' . $locale;
            if(!is_dir($locale_abs))
                continue;
            $target_locale_file = $target_locale_dir . '/' . $locale . '.php';
            $result = self::generateLocale($locale_abs);

            $source = to_source($result);
            $tx = '<?php' . $nl;
            $tx.= '/* GENERATE BY CLI */' . $nl;
            $tx.= '/* DON\'T MODIFY */' . $nl;
            $tx.= $nl;
            $tx.= 'return ' . $source . ';';

            Fs::write($target_locale_dir . '/' . $locale . '.php', $tx);
        }
    }
}