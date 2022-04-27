<?php
/**
 * Lib Cache
 * @package lib-cache
 * @version 0.0.2
 */

return [
    '__name' => 'lib-cache',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getphun/lib-cache.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'etc/cache/lib-cache' => ['install', 'remove'],
        'modules/lib-cache' => ['install', 'update', 'remove']
    ],
    '__dependencies' => [
        'required' => [],
        'optional' => []
    ],
    '__gitignore' => [
        'etc/cache/lib-cache/*' => true,
        '!etc/cache/lib-cache/.gitkeep' => true
    ],
    'autoload' => [
        'classes' => [
            'LibCache\\Driver' => [
                'type' => 'file',
                'base' => 'modules/lib-cache/driver'
            ],
            'LibCache\\Iface' => [
                'type' => 'file',
                'base' => 'modules/lib-cache/interface'
            ],
            'LibCache\\Service' => [
                'type' => 'file',
                'base' => 'modules/lib-cache/service'
            ]
        ]
    ],

    'libCache' => [
        'driver' => 'file',
        'file' => [
            'base' => null
        ],
        'handlers' => [
            'file' => 'LibCache\\Driver\\File'
        ]
    ],

    'service' => [
        'cache' => 'LibCache\\Service\\Cache'
    ]
];