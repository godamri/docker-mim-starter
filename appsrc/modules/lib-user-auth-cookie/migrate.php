<?php

return [
    'LibUserAuthCookie\\Model\\UserAuthCookie' => [
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
            'user' => [
                'type' => 'INT',
                'attrs' => [
                    'null' => false,
                    'unsigned' => true 
                ],
                'index' => 2000
            ],
            'hash' => [
                'type' => 'VARCHAR',
                'length' => 190,
                'attrs' => [
                    'unique' => true,
                    'null' => false 
                ],
                'index' => 3000
            ],
            'expires' => [
                'type' => 'DATETIME',
                'attrs' => [
                    'null' => false 
                ],
                'index' => 4000
            ],
            'updated' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 5000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 6000
            ]
        ]
    ]
];