<?php
/**
 * Server tester
 * @package core
 * @version 0.0.1
 */

namespace Mim\Server;

class PHP
{
    static function version(): array{
        $result = [
            'success' => version_compare(PHP_VERSION, '7.3', '>='),
            'info'    => PHP_VERSION
        ];
        
        return $result;
    }
}