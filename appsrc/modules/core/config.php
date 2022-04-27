<?php

return [
    '__name' => 'core',
    '__version' => '1.8.4',
    '__git' => 'git@github.com:getphun/core.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'index.php' => ['install','update','remove'],
        '.gitignore' => ['remove'],
        'modules/.gitkeep' => ['remove'],
        'app' => ['install','remove'],
        'etc' => ['install','remove'],
        'modules/core' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [],
        'optional' => []
    ],
    '__inject' => [
        [
            'name' => 'name',
            'question' => 'Application name',
            'default' => [
                'class' => 'Mim\\Provider\\Cli',
                'method' => 'dName'
            ],
            'rule' => '!^.+$!'
        ],
        [
            'name' => 'version',
            'question' => 'Application version',
            'default' => '0.0.1',
            'rule' => '!^[0-9]+\\.[0-9]+\\.[0-9]+$!'
        ],
        [
            'name' => 'host',
            'question' => 'Application hostname, without scheme',
            'default' => [
                'class' => 'Mim\\Provider\\Cli',
                'method' => 'dHost'
            ],
            'rule' => '!^[a-z0-9-\\.]+$!'
        ],
        [
            'name' => 'timezone',
            'question' => 'Application timezone',
            'default' => [
                'class' => 'Mim\\Provider\\Cli',
                'method' => 'dTimezone'
            ],
            'rule' => '!^.+$!'
        ],
        [
            'name' => 'install',
            'question' => 'Application installation time',
            'default' => [
                'class' => 'Mim\\Provider\\Cli',
                'method' => 'dInstall'
            ],
            'rule' => '!^.+$!'
        ],
        [
            'name' => 'secure',
            'question' => 'Use `https` scheme',
            'default' => FALSE,
            'rule' => 'boolean'
        ],
        [
            'name' => 'shared',
            'question' => 'Shared module dir',
            'default' => ''
        ],
        [
            'name' => '__gitignore',
            'question' => 'Would you like to keep the modules dir in repository',
            'default' => FALSE,
            'rule' => 'boolean',
            'injector' => [
                'class' => 'Mim\\Provider\\Cli',
                'method' => 'iGitIgnore'
            ]
        ]
    ],
    '__gitignore' => [
        'modules/*' => TRUE,
        '!modules/.gitkeep' => TRUE,
        'etc/cache/*' => TRUE,
        '!etc/cache/.gitkeep' => TRUE,
        'etc/cert/*' => TRUE,
        '!etc/cert/.gitkeep' => TRUE,
        'etc/config/development.php' => TRUE,
        'etc/config/production.php' => TRUE,
        'etc/config/test.php' => TRUE,
        'etc/log/access/*' => TRUE,
        '!etc/log/access/.gitkeep' => TRUE,
        'etc/log/error/*' => TRUE,
        '!etc/log/error/.gitkeep' => TRUE,
        'etc/temp/*' => TRUE,
        '!etc/temp/.gitkeep' => TRUE,
        'vendor' => TRUE
    ],
    'autoload' => [
        'classes' => [
            'Mim' => [
                'type' => 'file',
                'base' => 'modules/core/Mim.php'
            ],
            'Mim\\Controller' => [
                'type' => 'file',
                'base' => 'modules/core/system/Controller.php'
            ],
            'Mim\\Iface' => [
                'type' => 'file',
                'base' => 'modules/core/interface'
            ],
            'Mim\\Library' => [
                'type' => 'file',
                'base' => 'modules/core/library'
            ],
            'Mim\\Middleware' => [
                'type' => 'file',
                'base' => 'modules/core/system/Middleware.php'
            ],
            'Mim\\Provider' => [
                'type' => 'file',
                'base' => 'modules/core/provider'
            ],
            'Mim\\Service' => [
                'type' => 'file',
                'base' => 'modules/core/system/Service.php',
                'children' => 'modules/core/service'
            ],
            'Mim\\Server' => [
                'type' => 'file',
                'base' => 'modules/core/server'
            ],
            'StableSort' => [
                'type' => 'file',
                'base' => 'modules/core/third-party/StableSort'
            ],
            'Core\\Library' => [
                'type' => 'file',
                'base' => 'modules/core/library'
            ]
        ],
        'files' => [
            'modules/core/helper/global.php' => TRUE
        ]
    ],
    'service' => [
        'config' => 'Mim\\Service\\Config',
        'req' => 'Mim\\Service\\Request',
        'router' => 'Mim\\Service\\Router',
        'res' => 'Mim\\Service\\Response'
    ],
    'server' => [
        'core' => [
            'PHP >= 7.3' => 'Mim\\Server\\PHP::version'
        ]
    ],
    'callback' => [
        'app' => [
            'reconfig' => [
                'Core\\Library\\Config::reconfig' => true
            ]
        ]
    ]
];
