<?php

return [
    '__name' => 'post',
    '__version' => '0.1.3',
    '__git' => 'git@github.com:getmim/post.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/post' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-model' => NULL
            ],
            [
                'lib-formatter' => NULL
            ],
            [
                'lib-user' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'Post\\Model' => [
                'type' => 'file',
                'base' => 'modules/post/model'
            ]
        ],
        'files' => []
    ],
    'libEnum' => [
        'enums' => [
            'post.status' => ['Deleted', 'Draft', 'Editor', 'Published'],
            'content-pricing.type' => ['post' => 'Post']
        ]
    ],
    'libFormatter' => [
        'formats' => [
            'post' => [
                'id' => [
                    'type' => 'number'
                ],
                'user' => [
                    'type' => 'object',
                    'model' => [
                        'name'  => 'LibUser\\Library\\Fetcher',
                        'field' => 'id',
                        'type'  => 'number'
                    ],
                    'format' => 'user'
                ],
                'publisher' => [
                    'type' => 'object',
                    'model' => [
                        'name'  => 'LibUser\\Library\\Fetcher',
                        'field' => 'id',
                        'type'  => 'number'
                    ],
                    'format' => 'user'
                ],
                'status' => [
                    'type' => 'enum',
                    'enum' => 'post.status',
                    'vtype' => 'int'
                ],
                'title' => [
                    'type' => 'text'
                ],
                'slug' => [
                    'type' => 'text'
                ],
                'cover' => [
                    'type' => 'std-cover'
                ],
                'embed' => [
                    'type' => 'embed'
                ],
                'reff' => [
                    'type' => 'json'
                ],
                'meta' => [
                    'type' => 'json'
                ],
                'words' => [
                    'type' => 'number'
                ],
                'content' => [
                    'type' => 'partial',
                    'model' => [
                        'name'  => 'Post\\Model\\PostContent',
                        'field' => 'post'
                    ],
                    'field' => [
                        'name' => 'text',
                        'type' => 'text'
                    ],
                    'format' => 'post-content'
                ],
                'featured' => [
                    'type' => 'boolean'
                ],
                'editor_pick' => [
                    'type' => 'boolean'
                ],
                'published' => [
                    'type' => 'date'
                ],
                'updated' => [
                    'type' => 'date'
                ],
                'created' => [
                    'type' => 'date'
                ]
            ],
            'post-content' => [
                'id' => [
                    'type' => 'number'
                ],
                'post' => [
                    'type' => 'object',
                    'model' => [
                        'name'  => 'Post\\Model\\Post',
                        'field' => 'id',
                        'type'  => 'number'
                    ],
                    'format' => 'post'
                ],
                'text' => [
                    'type' => 'text'
                ],
                'updated' => [
                    'type' => 'date'
                ],
                'created' => [
                    'type' => 'date'
                ]
            ],
            'content-pricing' => [
                'object' => [
                    'cases' => [
                        'post' => [
                            'model' => [
                                'name' => 'Post\\Model\\Post',
                                'field' => 'id'
                            ],
                            'format' => 'post'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'contentPricing' => [
        'active' => [
            'post' => true
        ],
        'objects' => [
            'post' => [
                'model' => 'Post\\Model\\Post',
                'format' => 'post',
                'fields' => [
                    'id'        => 'id',
                    'user'      => 'user',
                    'title'     => 'title',
                    'created'   => 'created',
                    'published' => 'published',
                    'status'    => 'status'
                ]
            ]
        ]
    ]
];
