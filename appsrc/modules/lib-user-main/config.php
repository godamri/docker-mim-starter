<?php

return [
    '__name' => 'lib-user-main',
    '__version' => '1.0.0',
    '__git' => 'git@github.com:getmim/lib-user-main.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-user-main' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-user' => NULL
            ],
            [
                'lib-model' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibUserMain\\Handler' => [
                'type' => 'file',
                'base' => 'modules/lib-user-main/handler'
            ],
            'LibUserMain\\Model' => [
                'type' => 'file',
                'base' => 'modules/lib-user-main/model'
            ]
        ],
        'files' => []
    ],
    'libUser' => [
        'handler' => 'LibUserMain\\Handler\\User'
    ],
    'libUserMain' => [
        'login' => [
            'by' => [
                'name'  => true
            ]
        ]
    ]
];
