<?php

return [
    'name' => 'mimINIT',
    'version' => '0.0.1',
    'host' => getenv('SITE_HOST', 'localhost'),
    'timezone' => 'Asia/Jakarta',
    'install' => '2022-04-28 13:30:01',
    'secure' => FALSE,
    'shared' => '~',
    'libModel' => [
        'connections' => [
            'default' => [
                'driver' => 'mysql',
                'configs' => [
                    'main' => [
                        'host' => 'localhost',
                        'user' => 'root',
                        'dbname' => 'mim_init',
                        'passwd' => ''
                    ]
                ]
            ]
        ]
    ]
];