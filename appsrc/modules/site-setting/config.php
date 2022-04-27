<?php

return [
    '__name' => 'site-setting',
    '__version' => '1.1.1',
    '__git' => 'git@github.com:getmim/site-setting.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/site-setting' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-model' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'SiteSetting\\Service' => [
                'type' => 'file',
                'base' => 'modules/site-setting/service'
            ],
            'SiteSetting\\Model' => [
                'type' => 'file',
                'base' => 'modules/site-setting/model'
            ],
            'SiteSetting\\Library' => [
                'type' => 'file',
                'base' => 'modules/site-setting/library'
            ]
        ],
        'files' => []
    ],
    'service' => [
        'setting' => 'SiteSetting\\Service\\Setting'
    ],
    'libEvent' => [
        'events' => [
            'site-setting:updated' => [
                'SiteSetting\\Library\\Event::clear' => true
            ]
        ]
    ]
];
