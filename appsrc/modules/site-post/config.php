<?php

return [
    '__name' => 'site-post',
    '__version' => '0.1.0',
    '__git' => 'git@github.com:getmim/site-post.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'app/site-post' => ['install','remove'],
        'modules/site-post' => ['install','update','remove'],
        'theme/site/post' => ['install','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'post' => NULL
            ],
            [
                'site' => NULL
            ],
            [
                'site-meta' => NULL
            ],
            [
                'lib-formatter' => NULL
            ]
        ],
        'optional' => [
            [
                'lib-event' => NULL
            ],
            [
                'lib-cache-output' => NULL
            ]
        ]
    ],
    'autoload' => [
        'classes' => [
            'SitePost\\Controller' => [
                'type' => 'file',
                'base' => ['modules/site-post/controller','app/site-post/controller']
            ],
            'SitePost\\Library' => [
                'type' => 'file',
                'base' => 'modules/site-post/library'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'site' => [
            'sitePostSingle' => [
                'path' => [
                    'value' => '/post/read/(:slug)',
                    'params' => [
                        'slug' => 'slug'
                    ]
                ],
                'method' => 'GET',
                'handler' => 'SitePost\\Controller\\Post::single'
            ],
            'sitePostFeed' => [
                'path' => [
                    'value' => '/post/feed.xml'
                ],
                'method' => 'GET',
                'handler' => 'SitePost\\Controller\\Robot::feed'
            ]
        ]
    ],
    'libFormatter' => [
        'formats' => [
            'post' => [
                'page' => [
                    'type' => 'router',
                    'router' => [
                        'name' => 'sitePostSingle',
                        'params' => [
                            'slug' => '$slug'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'libEvent' => [
        'events' => [
            'post:created' => [
                'SitePost\\Library\\Event::clear' => TRUE
            ],
            'post:deleted' => [
                'SitePost\\Library\\Event::clear' => TRUE
            ],
            'post:updated' => [
                'SitePost\\Library\\Event::clear' => TRUE
            ]
        ]
    ],
    'site' => [
        'robot' => [
            'feed' => [
                'SitePost\\Library\\Robot::feed' => TRUE
            ],
            'sitemap' => [
                'SitePost\\Library\\Robot::sitemap' => TRUE
            ]
        ]
    ]
];
