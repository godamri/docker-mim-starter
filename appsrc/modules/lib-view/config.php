<?php
/**
 * Lib View
 * @package lib-view
 * @version 0.0.1
 */

return [
    '__name' => 'lib-view',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getphun/lib-view.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'theme' => ['install', 'remove'],
        'modules/lib-view' => ['install', 'update', 'remove']
    ],
    '__dependencies' => [
        'required' => [],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibView\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-view/library'
            ],
            'LibView\\Iface' => [
                'type' => 'file',
                'base' => 'modules/lib-view/interface'
            ],
            'LibView\\Renderer' => [
                'type' => 'file',
                'base' => 'modules/lib-view/renderer'
            ]
        ]
    ],

    'libView' => [
        'renderer' => 'phtml',
        'handlers' => [
            'phtml' => 'LibView\Renderer\PHtml'
        ]
    ]
];