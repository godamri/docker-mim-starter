<?php
/**
 * Meta
 * @package site-meta
 * @version 0.1.5
 */

namespace SiteMeta\Service;

class Meta extends \Mim\Service
{
    private $site_setting;

    private $meta_attr_name = [
        'article:author'                => 'property',
        'article:modified_time'         => 'property',
        'article:published_time'        => 'property',
        'article:publisher'             => 'property',
        'article:section'               => 'property',
        'article:tag'                   => 'property',

        'fb:admins'                     => 'property',
        'fb:app_id'                     => 'property',
        'fb:pages'                      => 'property',
        'fb:profile_id'                 => 'property',

        'alexaVerifyID'                 => 'name',
        'msvalidate.01'                 => 'name',
        'google-site-verification'      => 'name',
        'yandex-verification'           => 'name',
        'p:domain_verify'               => 'name',

        'description'                   => 'name',
        'keywords'                      => 'name',
        'generator'                     => 'name',

        'format-detection'              => 'name',

        'og:description'                => 'property',
        'og:locale'                     => 'property',
        'og:image'                      => 'property',
        'og:site_name'                  => 'property',
        'og:title'                      => 'property',
        'og:type'                       => 'property',
        'og:url'                        => 'property',
        'og:updated_time'               => 'property',

        'profile:username'              => 'property',
        'profile:first_name'            => 'property',
        'profile:last_name'             => 'property',

        'twitter:card'                  => 'name',
        'twitter:description'           => 'name',
        'twitter:image:src'             => 'name',
        'twitter:title'                 => 'name',
        'twitter:url'                   => 'name',

        'theme-color'                   => 'name',

        'viewport'                      => 'name'
    ];

    private $meta_default = [
        'viewport'      => 'viewport',
        'alexaVerifyID' => 'al_verification',
        'msvalidate.01' => 'bi_verification',
        'fb:app_id'     => 'fb_appid',
        'fb:pages'      => 'fb_pageid',
        'google-site-verification'  => 'go_verification',
        'p:domain_verify'   => 'pi_verification',
        'yandex-verification' => 'ya_verificationn',
        'theme-color'   => 'color'
    ];

    private $shared_metas = [
        'description' => [
            'description',
            'og:description',
            'twitter:description'
        ],
        'image' => [
            'og:image',
            'twitter:image:src'
        ],
        'published_time' => [
            'article:published_time'
        ],
        'title' => [
            'og:title',
            'twitter:title'
        ],
        'type' => [
            'og:type'
        ],
        'updated_time' => [
            'article:modified_time',
            'og:updated_time'
        ],
        'url' => [
            'og:url',
            'twitter:url'
        ]
    ];

    private function defaultValue(string $key, array $opts): ?string{
        if(isset($opts[$key]))
            return $opts[$key];

        $meta_key = 'meta.' . $key;
        $app = &\Mim::$app;
        if($this->site_settigs && isset($app->setting->$meta_key))
            return $app->setting->$meta_key;

        return $app->config->siteMeta->params->$key ?? $app->config->$key ?? '';
    }

    public function schemaOrg($name){
        $site = \Mim::$app->router->to('siteHome');

        $result = [
            '@context'      => 'http://schema.org/',
            '@type'         => 'Organization',
            'name'          => $name,
            'url'           => $site,
            'logo'          => [
                '@type'         => 'ImageObject',
                'url'           => \Mim::$app->router->asset('site', 'logo/200x60.png'),
                'height'        => 60,
                'width'         => 200
            ]
        ];

        if(!module_exists('site-setting'))
            return $result;

        $socials = [
            'dribbble',
            'facebook',
            'flicker',
            'google_plus',
            'instagram',
            'linkedin',
            'myspace',
            'pinterest',
            'soundcloud',
            'tumblr',
            'twitter',
            'vimeo',
            'youtube'
        ];
        $sameAs  = [];

        foreach($socials as $soc){
            if(\Mim::$app->setting->{'app_social_'.$soc})
                $sameAs[] = \Mim::$app->setting->{'app_social_'.$soc};
        }

        if($sameAs)
            $result['sameAs'] = $sameAs;

        return $result;
    }

    public function head(array $opts): string{
        $this->site_settigs = module_exists('site-setting');
        $config = &\Mim::$app->config->siteMeta->params;
        $tx = '';
        $nl = is_dev() ? PHP_EOL : '';

        $site_name = hs($this->defaultValue('name', $opts));
        $site_title = hs($opts['title']);

        $tx = '<meta charset="utf-8">' . $nl;
        $tx.= '<meta content="IE=edge" http-equiv="X-UA-Compatible">' . $nl;

        $tags = [];

        $props = [
            'og:site_name' => $site_name
        ];

        foreach($this->meta_default as $name => $prop){
            $value = $this->defaultValue($prop, $opts);
            if(!$value)
                continue;

            if($name === 'viewport' && $value === 'responsive')
                $value = 'width=device-width, initial-scale=1, shrink-to-fit=no';

            if(is_string($value))
                $value = hs($value);
            $props[$name] = $value;
        }

        foreach($this->shared_metas as $name => $keys){
            $value = $opts[$name] ?? null;
            if(!$value)
                continue;

            if(is_string($value))
                $value = hs($value);

            if($name === 'type' && $value == 'article' && isset($props['fb:pages']))
                $props['article:publisher'] = $props['fb:pages'];

            foreach($keys as $key){
                $props[$key] = $value;
                if($key === 'twitter:image:src')
                    $props['twitter:card'] = 'summary_large_image';
            }
        }

        if(!isset($opts['metas']))
            $opts['metas'] = [];
        $opts['metas'] = array_replace($props, $opts['metas']);

        foreach($opts['metas'] as $name => $values){
            $attr = $this->meta_attr_name[$name] ?? null;
            if(!$attr)
                continue;

            if(is_array($values)){
                foreach($values as $value){
                    $tags[] = ['meta', [
                        $attr => $name,
                        'content' => $value
                    ]];
                }
            }else{
                $tags[] = ['meta', [
                    $attr => $name,
                    'content' => $values
                ]];
            }
        }

        // rss feed
        if(isset($opts['rss'])){
            $tags[] = ['link', [
                'rel'   => 'alternate',
                'href'  => $opts['rss'],
                'title' => $site_title,
                'type'  => 'application/rss+xml'
            ]];
        }

        // hreflangs
        if(isset($opts['locales'])){
            foreach($opts['locales'] as $locale => $link){
                $tags[] = ['link', [
                    'rel'   => 'alternate',
                    'href'  => $link,
                    'hreflang'  => $locale
                ]];
            }
        }else{
            $locale = $this->defaultValue('locale', $opts);
            if($locale){
                $tags[] = ['link', [
                    'rel'   => 'alternate',
                    'href'  => $opts['url'],
                    'hreflang'  => $locale
                ]];
            }
        }

        // canonical
        $tags[] = ['link', [
            'rel'  => 'canonical',
            'href' => $opts['url']
        ]];

        // logos
        $logos = [
            'shortcut icon'     => [['48x48.png']],
            'apple-touch-icon'  => [['100x100.png'], ['72x72.png', '72x72'], ['114x114.png', '114x114']],
            'icon'              => [['192x192.png', '192x192']]
        ];
        foreach($logos as $name => $props){
            foreach($props as $prop){
                $atrs = [
                    'rel'  => $name,
                    'href' => \Mim::$app->router->asset('site', 'logo/' . $prop[0])
                ];
                if(isset($prop[1]))
                    $atrs['sizes'] = $prop[1];
                $tags[] = ['link', $atrs];
            }
        }

        // amphtml
        if(isset($opts['amphtml'])){
            $tags[] = ['link', [
                'rel'  => 'amphtml',
                'href' => $opts['amphtml']
            ]];
        }

        // custom css
        if(isset($opts['csses'])){
            foreach($opts['csses'] as $css){
                $tags[] = ['link', [
                    'rel'  => 'stylesheet',
                    'href' => $css
                ]];
            }
        }

        foreach($tags as $tag){
            $atrs = [];
            foreach($tag[1] as $tname => $tval)
                $atrs[] = $tname . '="' . $tval . '"';
            $tx.= '<' . $tag[0] . ' ' . implode(' ', $atrs) . '>' . $nl;
        }

        // title
        $title= $site_title . ' - ' . $site_name;
        $tx.= '<title>' . $title . '</title>' . $nl;

        // schema.org
        if(!isset($opts['schema.org']))
            $opts['schema.org'] = [];
        $opts['schema.org'][] = $this->schemaOrg($site_name);
        $jsopt = JSON_UNESCAPED_SLASHES;
        foreach($opts['schema.org'] as $schema)
            $tx.= '<script type="application/ld+json">' . json_encode($schema, $jsopt) . '</script>' . $nl;

        // google analytics
        if(!isset($opts['is_amp']) || !$opts['is_amp']){
            $ga_property = $this->defaultValue('ga_property', $opts);
            if($ga_property){
                $tx.= '<script>';
                $tx.=   '(function(i,s,o,g,r,a,m){';
                $tx.=       "i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){";
                $tx.=           '(i[r].q=i[r].q||[]).push(arguments)';
                $tx.=       '},i[r].l=1*new Date();';
                $tx.=       'a=s.createElement(o),m=s.getElementsByTagName(o)[0];';
                $tx.=       'a.async=1;a.src=g;m.parentNode.insertBefore(a,m)';
                $tx.=   '})(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');';
                $tx.=   'ga(\'create\',\'' . $ga_property . '\',\'auto\');';
                $tx.=   'ga(\'send\',\'pageview\');';
                $tx.= '</script>' . $nl;
            }

            $gtag_property = $this->defaultValue('gtag_property', $opts);
            if($gtag_property){
                $tx.= '<script async src="https://www.googletagmanager.com/gtag/js?id='.$gtag_property.'"></script>';
                $tx.= '<script>';
                $tx.=   'window.dataLayer = window.dataLayer || [];';
                $tx.=   'function gtag(){dataLayer.push(arguments);}';
                $tx.=   'gtag(\'js\', new Date());';
                $tx.=   'gtag(\'config\', \''.$gtag_property.'\');';
                $tx.= '</script>' . $nl;
            }
        }

        return $tx;
    }

    public function foot(array $opts=[]): string{
        $this->site_settigs = module_exists('site-setting');

        $config = &\Mim::$app->config->siteMeta->params;
        $tx = '';
        $nl = is_dev() ? PHP_EOL : '';

        $def_opts_keys = [
            'al_an_account',
            'al_an_domain',
            'fb_js',
            'fb_appid',
            'go_js',
            'ig_js',
            'tw_js'
        ];

        foreach($def_opts_keys as $key)
            $opts[$key] = $this->defaultValue($key, $opts);

        // jquery
        if(isset($opts['jquery'])){
            $version = is_bool($opts['jquery']) ? '3.3.1' : $opts['jquery'];
            $src = '//ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery.min.js';
            $tx.= '<script src="' . $src . '"></script>' . $nl;
        }

        // alexa analytics
        if($opts['al_an_account'] && $opts['al_an_domain']){
            $account = $opts['al_an_account'];
            $domain  = $opts['al_an_domain'];

            $tx.= '<script id="alexa-sdk">';
            $tx.=   '_atrk_opts={atrk_acct:"' . $account . '",domain:"' . $domain . '",dynamic: true};';
            $tx.=   '(function(){var as=document.createElement(\'script\');';
            $tx.=   'as.type=\'text/javascript\';';
            $tx.=   'as.async=true;';
            $tx.=   'as.src="https://d31qbv1cthcecs.cloudfront.net/atrk.js";';
            $tx.=   'var s=document.getElementsByTagName(\'script\')[0];';
            $tx.=   's.parentNode.insertBefore(as,s);})();';
            $tx.= '</script>' . $nl;
            $tx.= '<noscript>';
            $tx.=   '<img src="https://d5nxst8fruw4z.cloudfront.net/atrk.gif?account=' . $account . '" style="display:none" height="1" width="1" alt="">';
            $tx.= '</noscript>' . $nl;
        }

        // facebook js
        if($opts['fb_js']){
            $src= '//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.6&appId=' . $opts['fb_appid'];
            $tx.= '<script async defer src="' . $src . '" id="facebook-jssdk"></script>' . $nl;
        }

        // google js
        if($opts['go_js'])
            $tx.= '<script async defer src="//apis.google.com/js/platform.js"></script>' . $nl;

        // instagram js
        if($opts['ig_js'])
            $tx.= '<script async defer src="//platform.instagram.com/en_US/embeds.js"></script>' . $nl;

        // twitter js
        if($opts['tw_js'])
            $tx.= '<script async defer src="//platform.twitter.com/widgets.js"></script>' . $nl;

        // other js
        if(isset($opts['jses'])){
            foreach($opts['jses'] as $js)
                $tx.= '<script src="' . $js . '"></script>' . $nl;
        }

        return $tx;
    }
}
