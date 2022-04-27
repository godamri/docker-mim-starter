<?php
/**
 * CLI Provider
 * @package core
 * @version 0.0.2
 */

namespace Mim\Provider;

class Cli
{
    static function dTimezone(){
        $date = new \DateTime();
        $tzone = $date->getTimezone()->getName();
        if(!$tzone)
            $tzone = 'Asia/Jakarta';
        return $tzone;
    }

    static function dHost(){
        $here = getcwd();
        $base = basename(dirname($here));
        $base = preg_replace('![^a-z0-9.]!', '-', \strtolower($base));
        $base = preg_replace('!-+!', '-', $base);

        return $base . '.mim';
    }

    static function dInstall(){
        return date('Y-m-d H:i:s');
    }

    static function dName(){
        $here = getcwd();
        $base = basename(dirname($here));
        $base = preg_replace('![^a-zA-Z0-9]!', ' ', $base);
        $base = preg_replace('! +!', ' ', $base);

        return ucwords($base);
    }

    static function iGitIgnore(array $config, bool $value): ?array{
        if(!$value)
            return null;

        return [
            'modules/*' => null,
            '!modules/.gitkeep' => null
        ];
    }
}