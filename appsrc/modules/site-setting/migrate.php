<?php

return [
    'SiteSetting\\Model\\SiteSetting' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'primary_key' => true,
                    'auto_increment' => true 
                ],
                'index' => 1000
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false,
                    'unique' => true,
                ],
                'index' => 2000
            ],
            'type' => [
                'comment' => '1 inlne text, 2 date, 3 number, 4 boolean, 5 multiline text, 6 url, 7 email, 8 color',
                'type' => 'TINYINT',
                'length' => 1,
                'attrs' => [
                    'null' => false,
                    'unsigned' => true,
                    'default' => 1
                ],
                'index' => 3000
            ],
            'group' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false
                ],
                'index' => 4000
            ],
            'value' => [
                'type' => 'TEXT',
                'index' => 5000
            ],
            'updated' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 6000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 7000
            ]
        ]
    ]
];