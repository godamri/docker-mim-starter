<?php

return [
    'Post\\Model\\Post' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'primary_key' => TRUE,
                    'auto_increment' => TRUE
                ],
                'index' => 1000
            ],
            'user' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'index' => 2000
            ],
            'publisher' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE
                ],
                'index' => 3000
            ],
            'status' => [
                'comment' => '0 Deleted, 1 Draft, 2 Editor, 3 Published',
                'type' => 'TINYINT',
                'attrs' => [
                    'unsigned' => true,
                    'null' => false,
                    'default' => 1
                ],
                'index' => 4000
            ],
            'title' => [
                'type' => 'VARCHAR',
                'length' => 150,
                'attrs' => [
                    'null' => FALSE
                ],
                'index' => 5000
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'length' => 150,
                'attrs' => [
                    'null' => FALSE,
                    'unique' => TRUE
                ],
                'index' => 6000
            ],
            'cover' => [
                'type' => 'TEXT',
                'attrs' => [],
                'index' => 7000
            ],
            'embed' => [
                'type' => 'TEXT',
                'attrs' => [],
                'index' => 8000
            ],
            'reff' => [
                'comment' => 'ex: ["url",...]',
                'type' => 'TEXT',
                'attrs' => [],
                'index' => 9000
            ],
            'words' => [
                'type' => 'INT',
                'attrs' => [
                    'null' => false,
                    'unsigned' => true 
                ],
                'index' => 10000
            ],
            'featured' => [
                'type' => 'BOOLEAN',
                'attrs' => [
                    'default' => FALSE
                ],
                'index' => 11000
            ],
            'editor_pick' => [
                'type' => 'BOOLEAN',
                'attrs' => [
                    'default' => FALSE
                ],
                'index' => 12000
            ],
            'meta' => [
                'type' => 'TEXT',
                'attrs' => [],
                'index' => 13000
            ],
            'published' => [
                'type' => 'DATETIME',
                'attrs' => [
                    'null' => true 
                ],
                'index' => 14000
            ],
            'updated' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 15000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 16000
            ]
        ]
    ],
    'Post\\Model\\PostContent' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'primary_key' => TRUE,
                    'auto_increment' => TRUE
                ],
                'index' => 1000
            ],
            'post' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'index' => 2000
            ],
            'text' => [
                'type' => 'TEXT',
                'attrs' => [],
                'index' => 3000
            ],
            'updated' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 10000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 11000
            ]
        ]
    ]
];