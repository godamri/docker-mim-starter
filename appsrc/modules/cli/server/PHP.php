<?php
/**
 * Server tester
 * @package cli
 * @version 0.0.1
 */

namespace Cli\Server;

class PHP
{
    static function readline(): array{
        $exists = extension_loaded('readline');
        $result = [
            'success' => $exists,
            'info'    => $exists ? '-' : 'Not installed'
        ];
        
        return $result;
    }
}