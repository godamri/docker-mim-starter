<?php

return [
    '__name' => 'lib-user-auth-cookie',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/lib-user-auth-cookie.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-user-auth-cookie' => ['install','update','remove']
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
            'LibUserAuthCookie\\Authorizer' => [
                'type' => 'file',
                'base' => 'modules/lib-user-auth-cookie/authorizer'
            ],
            'LibUserAuthCookie\\Model' => [
                'type' => 'file',
                'base' => 'modules/lib-user-auth-cookie/model'
            ]
        ],
        'files' => []
    ],
    'libUserAuthCookie' => [
        'cookie' => '_mu',
        'expires' => 604800
    ],
    'libUser' => [
        'authorizers' => [
            'cookie' => 'LibUserAuthCookie\\Authorizer\\Cookie'
        ]
    ]
];
