<?php

return [
    '__name' => 'lib-model-mysql',
    '__version' => '0.5.1',
    '__git' => 'git@github.com:getmim/lib-model-mysql.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-model-mysql' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-model' => NULL
            ]
        ],
        'optional' => []
    ],
    '__inject' => [
        [
            'name' => 'libModel',
            'question' => 'Would you like to add default connection',
            'default' => TRUE,
            'rule' => 'boolean',
            'injector' => [
                'class' => 'LibModelMysql\\Library\\Injector',
                'method' => 'iConnection'
            ]
        ]
    ],
    'autoload' => [
        'classes' => [
            'LibModelMysql\\Driver' => [
                'type' => 'file',
                'base' => 'modules/lib-model-mysql/driver'
            ],
            'LibModelMysql\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-model-mysql/library'
            ],
            'LibModelMysql\\Migrator' => [
                'type' => 'file',
                'base' => 'modules/lib-model-mysql/migrator'
            ]
        ],
        'files' => []
    ],
    'libModel' => [
        'drivers' => [
            'mysql' => 'LibModelMysql\\Driver\\MySQL'
        ],
        'migrators' => [
            'mysql' => 'LibModelMysql\\Migrator\\MySQL'
        ]
    ]
];
