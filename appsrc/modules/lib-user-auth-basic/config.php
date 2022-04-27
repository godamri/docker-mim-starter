<?php

return [
    '__name' => 'lib-user-auth-basic',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/lib-user-auth-basic.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-user-auth-basic' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-user' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibUserAuthBasic\\Authorizer' => [
                'type' => 'file',
                'base' => 'modules/lib-user-auth-basic/authorizer'
            ]
        ],
        'files' => []
    ],
    'libUser' => [
        'authorizers' => [
            'basic' => 'LibUserAuthBasic\\Authorizer\\Basic'
        ]
    ]
];