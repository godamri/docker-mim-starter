<?php

return [
    '__name' => 'lib-formatter',
    '__version' => '0.8.0',
    '__git' => 'git@github.com:getmim/lib-formatter.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-formatter' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibFormatter\\Handler' => [
                'type' => 'file',
                'base' => 'modules/lib-formatter/handler'
            ],
            'LibFormatter\\Iface' => [
                'type' => 'file',
                'base' => 'modules/lib-formatter/interface'
            ],
            'LibFormatter\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-formatter/library'
            ],
            'LibFormatter\\Object' => [
                'type' => 'file',
                'base' => 'modules/lib-formatter/object'
            ],
        ],
        'files' => []
    ],
    'callback' => [
        'app' => [
            'reconfig' => [
                'LibFormatter\\Library\\Config::reconfig' => true
            ]
        ]
    ],
    'libFormatter' => [
        'handlers' => [
            'boolean' => [
                'handler' => 'LibFormatter\\Handler\\Main::boolean',
                'collective' => FALSE
            ],
            'bool' => [
                'handler' => 'LibFormatter\\Handler\\Main::boolean',
                'collective' => FALSE
            ],
            'clone' => [
                'handler' => 'LibFormatter\\Handler\\Main::clone',
                'collective' => FALSE
            ],
            'custom' => [
                'handler' => 'LibFormatter\\Handler\\Main::custom',
                'collective' => FALSE
            ],
            'date' => [
                'handler' => 'LibFormatter\\Handler\\Main::date',
                'collective' => FALSE
            ],
            'delete' => [
                'handler' => 'LibFormatter\\Handler\\Main::delete',
                'collective' => FALSE
            ],
            'embed' => [
                'handler' => 'LibFormatter\\Handler\\Main::embed',
                'collective' => FALSE
            ],
            'interval' => [
                'handler' => 'LibFormatter\\Handler\\Main::interval',
                'collective' => FALSE
            ],
            'location' => [
                'handler' => 'LibFormatter\\Handler\\Main::location',
                'collective' => FALSE
            ],
            'multiple-text' => [
                'handler' => 'LibFormatter\\Handler\\Main::multipleText',
                'collective' => FALSE
            ],
            'number' => [
                'handler' => 'LibFormatter\\Handler\\Main::number',
                'collective' => FALSE
            ],
            'text' => [
                'handler' => 'LibFormatter\\Handler\\Main::text',
                'collective' => FALSE
            ],
            'json' => [
                'handler' => 'LibFormatter\\Handler\\Main::json',
                'collective' => FALSE
            ],
            'join' => [
                'handler' => 'LibFormatter\\Handler\\Main::join',
                'collective' => FALSE
            ],
            'rename' => [
                'handler' => 'LibFormatter\\Handler\\Main::rename',
                'collective' => FALSE
            ],
            'router' => [
                'handler' => 'LibFormatter\\Handler\\Main::router',
                'collective' => FALSE
            ],
            'switch' => [
                'handler' => 'LibFormatter\\Handler\\Main::switch',
                'collective' => FALSE
            ]
        ]
    ]
];
