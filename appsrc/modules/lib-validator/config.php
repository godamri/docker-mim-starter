<?php

return [
    '__name' => 'lib-validator',
    '__version' => '1.3.3',
    '__git' => 'git@github.com:getmim/lib-validator.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-validator' => ['install','update','remove'],
        'etc/locale/en-US/form' => ['install','update','remove'],
        'etc/locale/id-ID/form' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-locale' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'LibValidator\\Validator' => [
                'type' => 'file',
                'base' => 'modules/lib-validator/validator'
            ],
            'LibValidator\\Filter' => [
                'type' => 'file',
                'base' => 'modules/lib-validator/filter'
            ],
            'LibValidator\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-validator/library'
            ]
        ],
        'files' => []
    ],
    'libValidator' => [
        'filters' => [
            'array' => 'LibValidator\\Filter\\General::array',
            'boolean' => 'LibValidator\\Filter\\General::boolean',
            'float' => 'LibValidator\\Filter\\General::float',
            'integer' => 'LibValidator\\Filter\\General::integer',
            'lowercase' => 'LibValidator\\Filter\\General::lowercase',
            'object' => 'LibValidator\\Filter\\General::object',
            'round' => 'LibValidator\\Filter\\General::round',
            'string' => 'LibValidator\\Filter\\General::string',
            'ucwords' => 'LibValidator\\Filter\\General::ucwords',
            'uppercase' => 'LibValidator\\Filter\\General::uppercase'
        ],
        'validators' => [
            'array' => 'LibValidator\\Validator\\General::array',
            'callback' => 'LibValidator\\Validator\\General::callback',
            'config' => 'LibValidator\\Validator\\General::config',
            'date' => 'LibValidator\\Validator\\General::date',
            'email' => 'LibValidator\\Validator\\General::email',
            'empty' => 'LibValidator\\Validator\\General::empty',
            'equals_to' => 'LibValidator\\Validator\\General::equalsTo',
            'file' => 'LibValidator\\Validator\\General::file',
            'in' => 'LibValidator\\Validator\\General::in',
            'ip' => 'LibValidator\\Validator\\General::ip',
            'json' => 'LibValidator\\Validator\\General::json',
            'length' => 'LibValidator\\Validator\\General::length',
            'object' => 'LibValidator\\Validator\\General::object',
            'notin' => 'LibValidator\\Validator\\General::notin',
            'numeric' => 'LibValidator\\Validator\\General::numeric',
            'regex' => 'LibValidator\\Validator\\General::regex',
            'required' => 'LibValidator\\Validator\\General::required',
            'required_on' => 'LibValidator\\Validator\\General::requiredOn',
            'text' => 'LibValidator\\Validator\\General::text',
            'url' => 'LibValidator\\Validator\\General::url'
        ],
        'errors' => [
            '1.0' => 'form.error.general.not_an_array',
            '1.1' => 'form.error.general.not_indexed_array',
            '1.2' => 'form.error.general.not_assoc_array',
            '2.0' => 'form.error.general.not_a_date',
            '2.1' => 'form.error.general.wrong_date_format',
            '2.2' => 'form.error.general.the_date_too_early',
            '2.3' => 'form.error.general.the_date_too_far',
            '3.0' => 'form.error.general.not_an_email',
            '4.0' => 'form.error.general.not_in_array',
            '5.0' => 'form.error.general.not_an_ip',
            '5.1' => 'form.error.general.not_an_ipv4',
            '5.2' => 'form.error.general.not_an_ipv6',
            '6.0' => 'form.error.general.too_short',
            '6.1' => 'form.error.general.too_long',
            '7.0' => 'form.error.general.in_array',
            '8.0' => 'form.error.general.not_numeric',
            '8.1' => 'form.error.general.too_less',
            '8.2' => 'form.error.general.too_great',
            '8.3' => 'form.error.general.decimal_not_match',
            '9.0' => 'form.error.general.not_an_object',
            '10.0' => 'form.error.general.not_match',
            '11.0' => 'form.error.general.required',
            '12.0' => 'form.error.general.not_a_text',
            '12.1' => 'form.error.general.not_a_slug',
            '12.2' => 'form.error.general.not_an_alnumdash',
            '12.3' => 'form.error.general.not_an_alpha',
            '12.4' => 'form.error.general.not_an_alnum',
            '13.0' => 'form.error.general.not_an_url',
            '13.1' => 'form.error.general.dont_have_path',
            '13.2' => 'form.error.general.dont_have_query',
            '13.3' => 'form.error.general.require_query_not_present',
            '21.0' => 'form.error.general.is_empty',
            '21.1' => 'form.error.general.is_not_empty',
            '23.1' => 'form.error.general.is_not_valid_json_string',
            '25.0' => 'form.error.general.is_not_in_acceptable_value',
            '25.1' => 'form.error.general.is_not_in_acceptable_list_values',
            '25.2' => 'form.error.general.is_not_match_with_requested_value',
            '26.1' => 'form.error.general.is_not_equal',
            '28.0' => 'form.error.general.is_not_file'
        ]
    ],

    'callback' => [
        'app' => [
            'reconfig' => [
                'LibValidator\\Library\\Config::reconfig' => true
            ]
        ]
    ]
];
