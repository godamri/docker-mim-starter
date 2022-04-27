<?php

return [
    'name' => 'TestMIm',
    'version' => '0.0.1',
    'host' => getenv('SITE_HOST'),
    'timezone' => 'Asia/Jakarta',
    'install' => '2022-04-27 15:08:47',
    'secure' => FALSE,
    'shared' => '~',
    '__gitignore' => [
        'modules/*' => NULL,
        '!modules/.gitkeep' => NULL
    ],
    'libModel' => [
        'connections' => [
            'default' => [
                'driver' => 'mysql',
                'configs' => [
                    'main' => [
                        'host' => getenv('DB_HOST'),
                        'user' => getenv('DB_USER'),
                        'dbname' => getenv('DB_NAME'),
                        'passwd' => getenv('DB_PASS'),
                    ]
                ]
            ]
        ]
    ]
];