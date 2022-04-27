<?php

return [
    'LibUserMain\\Model\\User' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'primary_key' => true,
                    'auto_increment' => true
                ],
                'index' => 0
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'unique' => true,
                    'null' => false
                ],
                'index' => 1000
            ],
            'fullname' => [
                'type' => 'VARCHAR',
                'length' => 100,
                'attrs' => [
                    'null' => false 
                ],
                'index' => 2000
            ],
            'password' => [
                'type' => 'VARCHAR',
                'length' => 150,
                'attrs' => [
                    'null' => false 
                ],
                'index' => 3000
            ],
            'avatar' => [
                'type' => 'VARCHAR',
                'length' => 250,
                'index' => 4000
            ],
            // 0 Deleted
            // 1 Banner
            // 2 Unverified
            // 3 Verified
            'status' => [
                'type' => 'TINYINT',
                'length' => 1,
                'attrs' => [
                    'unsigned' => true,
                    'default' => 2
                ],
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