<?php

return [
    '__name' => 'lib-enum',
    '__version' => '0.3.0',
    '__git' => 'git@github.com:getmim/lib-enum.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-enum' => ['install','update','remove'],
        'etc/locale/en-US/form/error/enum.php' => ['install','update','remove'],
        'etc/locale/id-ID/form/error/enum.php' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibEnum\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-enum/library'
            ],
            'LibEnum\\Formatter' => [
                'type' => 'file',
                'base' => 'modules/lib-enum/formatter'
            ],
            'LibEnum\\Validator' => [
                'type' => 'file',
                'base' => 'modules/lib-enum/validator'
            ]
        ],
        'files' => []
    ],
    'libEnum' => [
        'enums' => []
    ],
    'libFormatter' => [
        'handlers' => [
            'enum' => [
                'handler' => 'LibEnum\\Formatter\\Main::enum',
                'collective' => FALSE
            ],
            'multiple-enum' => [
                'handler' => 'LibEnum\\Formatter\\Main::multipleEnum',
                'collective' => FALSE
            ]
        ]
    ],
    'libValidator' => [
        'errors' => [
            '22.0' => 'form.error.enum.options_not_found',
            '22.1' => 'form.error.enum.selected_value_is_not_in_options',
            '22.2' => 'form.error.enum.one_or_more_selected_value_is_not_in_options'
        ],
        'validators' => [
            'enum' => 'LibEnum\\Validator\\Enum::in'
        ]
    ],
    'callback' => [
        'app' => [
            'reconfig' => [
                'LibEnum\\Library\\Config::reconfig' => TRUE
            ]
        ]
    ]
];
