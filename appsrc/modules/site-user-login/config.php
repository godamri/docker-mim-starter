<?php

return [
    '__name' => 'site-user-login',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/site-user-login.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'app/site-user-login' => ['install','remove'],
        'modules/site-user-login' => ['install','update','remove'],
        'theme/site/me' => ['install','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-user' => NULL
            ],
            [
                'lib-user-auth-cookie' => NULL
            ],
            [
                'lib-form' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'SiteUserLogin\\Controller' => [
                'type' => 'file',
                'base' => 'app/site-user-login/controller'
            ],
            'SiteUserLogin\\Library' => [
                'type' => 'file',
                'base' => 'modules/site-user-login/library'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'site' => [
            'siteMeLogin' => [
                'path' => [
                    'value' => '/me/login'
                ],
                'handler' => 'SiteUserLogin\\Controller\\Login::login',
                'method' => 'GET|POST'
            ],
            'siteMeLogout' => [
                'path' => [
                    'value' => '/me/logout'
                ],
                'handler' => 'SiteUserLogin\\Controller\\Login::logout'
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'site.me.login' => [
                'name' => [
                    'label' => 'Name',
                    'type' => 'text',
                    'nolabel' => TRUE,
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ],
                'password' => [
                    'label' => 'Password',
                    'nolabel' => TRUE,
                    'type' => 'password',
                    'meter' => FALSE,
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ]
            ]
        ]
    ]
];