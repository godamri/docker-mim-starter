<?php
/**
 * Meta
 * @package site-post
 * @version 0.1.0
 */

namespace SitePost\Library;


class Meta
{
    static function single(object $page){
        $result = [
            'head' => [],
            'foot' => []
        ];

        $home_url = \Mim::$app->router->to('siteHome');

        // reset meta
        if(!is_object($page->meta))
            $page->meta = (object)[];

        $def_meta = [
            'title'         => $page->title,
            'description'   => $page->content->chars(160),
            'schema'        => 'Article',
            'keyword'       => ''
        ];

        foreach($def_meta as $key => $value){
            if(!isset($page->meta->$key) || !$page->meta->$key)
                $page->meta->$key = $value;
        }

        $result['head'] = [
            'description'       => $page->meta->description,
            'published_time'    => $page->created,
            'schema.org'        => [],
            'type'              => 'article',
            'title'             => $page->meta->title,
            'updated_time'      => $page->updated,
            'url'               => $page->page,
            'metas'             => []
        ];

        // schema breadcrumbList
        $result['head']['schema.org'][] = [
            '@context'  => 'http://schema.org',
            '@type'     => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'item' => [
                        '@id' => $home_url,
                        'name' => \Mim::$app->config->name
                    ]
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'item' => [
                        '@id' => $home_url . '#post',
                        'name' => 'Posts'
                    ]
                ]
            ]
        ];

        // schema page
        $result['head']['schema.org'][] = [
            '@context'      => 'http://schema.org',
            '@type'         => $page->meta->schema,
            'name'          => $page->meta->title,
            'description'   => $page->meta->description,
            'dateCreated'   => $page->created,
            'dateModified'  => $page->updated,
            'datePublished' => $page->created,
            'publisher'     => \Mim::$app->meta->schemaOrg( \Mim::$app->config->name ),
            // 'thumbnailUrl'  => $meta_image,
            'url'           => $page->page,
            // 'image'         => $meta_image
        ];

        // amp html
        if (module_exists('site-post-amp')) {
            $route = \Mim::$app->req->route->name;
            if ($route != 'sitePostAmp') {
                $result['head']['amphtml'] = $page->amp;
            }else{
                $result['head']['is_amp'] = true;
            }
        }

        return $result;
    }
}
