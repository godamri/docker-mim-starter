<?php
/**
 * Injector
 * @package lib-model-mysql
 * @version 0.0.3
 */

namespace LibModelMysql\Library;

use Cli\Library\Bash;

class Injector
{
    static function iConnection(array $config, bool $value): ?array{
        if(!$value)
            return null;

        $db_name = 'mim_' . strtolower($config['name']);
        $db_name = preg_replace('![^a-z0-9]!', '_', $db_name);
        $db_name = preg_replace('!_+!', '_', $db_name);

        $con_host = Bash::ask(['text' => 'DB Hostname', 'default' => 'localhost']);
        $con_user = Bash::ask(['text' => 'DB User',     'default' => get_current_user()]);
        $con_db   = Bash::ask(['text' => 'DB Name',     'default' => $db_name]);
        $con_pass = Bash::ask(['text' => 'DB Password', 'default' => '']);

        $result = [
            'connections' => [
                'default' => [
                    'driver' => 'mysql',
                    'configs' => [
                        'main' => [
                            'host'   => $con_host,
                            'user'   => $con_user,
                            'dbname' => $con_db,
                            'passwd' => $con_pass
                        ]
                    ]
                ]
            ]
        ];

        $sql = 'CREATE DATABASE `' . $con_db . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
        $sql_len = strlen($sql);
        $sql_sep = str_repeat('-', $sql_len);

        Bash::echo('');
        Bash::echo('Please create the database with below script');
        Bash::echo($sql_sep);
        Bash::echo($sql);
        Bash::echo($sql_sep);
        Bash::echo('');

        return $result;
    }
}