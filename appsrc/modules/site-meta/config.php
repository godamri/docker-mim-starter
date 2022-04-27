<?php

return [
    '__name' => 'site-meta',
    '__version' => '0.1.5',
    '__git' => 'git@github.com:getmim/site-meta.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/site-meta' => ['install','update','remove'],
        'theme/site/static/logo' => ['install','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'site' => null
            ]
        ],
        'optional' => [
            [
                'site-setting' => NULL
            ]
        ]
    ],
    'autoload' => [
        'classes' => [
            'SiteMeta\\Service' => [
                'type' => 'file',
                'base' => 'modules/site-meta/service'
            ]
        ],
        'files' => []
    ],
    'service' => [
        'meta' => 'SiteMeta\\Service\\Meta'
    ],
    'siteMeta' => [
        'params' => [
            // 'al_an_account' => 'ALEXA_ACCOUNT',
            // 'al_an_domain' => 'ALEXA_DOMAIN',
            // 'al_verification' => 'ALEXA_VERIFICATION',
            // 'bi_verification' => 'BING_VERIFICATION',
            // 'color' => '#000000',
            'fb_js' => false,
            // 'fb_appid' => 'FB_APPID',
            // 'fb_pageid' => 'FB_PAGEID',
            'go_js' => false,
            // 'go_verification' => 'GOOGLE_VERIFICATION',
            // 'ga_property' => 'GA:PROVERTY',
            'ig_js' => false,
            // 'locale' => 'en-US',
            // 'name' => 'My Powerfull Site',
            // 'pi_verification' => 'PINTEREST_VERIFICATION',
            'viewport' => 'responsive',
            'tw_js' => false,
            // 'ya_verificationn' => 'YANDEX_VERIFICATION'
        ]
    ]
];
