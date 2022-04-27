<?php
/**
 * Robot
 * @package site
 * @version 0.0.1
 */

namespace Site\Library;

class Robot
{

    static function feed(): array {
        $route = \Mim::$app->router->to('siteHome');

        $title = \Mim::$app->config->name;
        $desc  = $title;

        $page = (object)[
            'description'   => $desc,
            'page'          => $route,
            'published'     => \Mim::$app->config->install,
            'updated'       => date('Y-m-d H:i:s'),
            'priority'      => '0.5',
            'title'         => $title,
            'changefreq'    => 'hourly',
            'guid'          => $route,
            'image'         => [
                'url'           => \Mim::$app->router->asset('site', '/logo/192x192.png'),
                'caption'       => $title,
                'title'         => $title
            ]
        ];

        return [$page];
    }

    static function sitemap(): array {
        $route = \Mim::$app->router->to('siteHome');
        $title = \Mim::$app->config->name;
        $desc  = $title;

        $page = (object)[
            'page'          => $route,
            'updated'       => date('Y-m-d H:i:s'),
            'priority'      => '0.8',
            'changefreq'    => 'hourly'
        ];

        return [$page];
    }
}