<?php
/**
 * CLI Base
 * @package cli
 * @version 0.0.7
 */

return [
    '__name' => 'cli',
    '__version' => '0.3.2',
    '__git' => 'git@github.com:getphun/cli.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'etc/bash'    => ['install', 'update', 'remove'],
        'etc/zsh'    => ['install', 'update', 'remove'],
        'modules/cli' => ['install', 'update', 'remove'],
        'mim'         => ['install', 'update', 'remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'core' => null
            ]
        ],
        'optional' => []
    ],
    '__gitignore' => [
        'etc/bash/au.sh' => true,
        'mim' => true
    ],
    'autoload' => [
        'classes' => [
        	'Cli\\Autocomplete' => [
        		'type' => 'file',
        		'base' => 'modules/cli/system/Autocomplete.php'
        	],
            'Cli\\Controller' => [
                'type' => 'file',
                'base' => 'modules/cli/system/Controller.php',
                'children' => 'modules/cli/controller'
            ],
            'Cli\\Library' => [
                'type' => 'file',
                'base' => 'modules/cli/library',
            ],
            'Cli\\Server' => [
                'type' => 'file',
                'base' => 'modules/cli/server'
            ]
        ]
    ],
    'gates' => [
        'tool' => [
            'priority' => 1,
            'host' => [
                'value' => 'CLI'
            ],
            'path' => [
                'value' => ''
            ]
        ]
    ],
    'routes' => [
        'tool' => [
            '404' => [
                'handler' => 'Cli\\Controller::show404'
            ],
            '500' => [
                'handler' => 'Cli\\Controller::show500'
            ],
            'toolAutocomplete' => [
                'info' => 'Internal autocomplete provider',
                'skipHelp' => true,
                'path' => [
                    'value' => 'autocomplete'
                ],
                'handler' => 'Cli\\Controller\\Tool::autocomplete'
            ],
            'toolAutocompleteArgs' => [
                'info' => 'Internal autocomplete provider',
                'skipHelp' => true,
                'path' => [
                    'value' => 'autocomplete (:command)',
                    'params'=> [
                        'command' => 'rest'
                    ]
                ],
                'handler' => 'Cli\\Controller\\Tool::autocomplete'
            ],
            'toolHelp' => [
                'info' => 'Show this information',
                'path' => [
                    'value' => 'help'
                ],
                'handler' => 'Cli\\Controller\\Tool::help'
            ],
            'toolVersion' => [
                'info' => 'Show installed tools version',
                'path' => [
                    'value' => 'version'
                ],
                'handler' => 'Cli\\Controller\\Tool::version'
            ]
        ]
    ],
    'server' => [
        'cli' => [
            'Readline' => 'Cli\\Server\\PHP::readline'
        ]
    ],

    'cli' => [
        'autocomplete' => [
            '!^(help|version)$!' => [
                'priority' => 2,
                'handler' => [
                    'class' => 'Cli\\Library\\Autocomplete',
                    'method' => 'none'
                ]
            ],
            '!^[a-z]*$!' => [
                'priority' => 1,
                'handler' => [
                    'class' => 'Cli\\Library\\Autocomplete',
                    'method' => 'primary'
                ]
            ]
        ]
    ]
];
