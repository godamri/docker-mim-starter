<?php
/**
 * Meta
 * @package site
 * @version 0.3.0
 */

namespace Site\Library;


class Meta
{
    static function single(){
        $result = [
            'head' => [],
            'foot' => []
        ];

        $home_url = \Mim::$app->router->to('siteHome');

        $deff = \Mim::$app->config->name;

        $site_setting = module_exists('site-setting');

        $std_metas = ['title','description','keywords'];
        $meta = (object)[];
        foreach($std_metas as $name){
            $value = $deff;
            if($site_setting)
                $value = \Mim::$app->setting->{'frontpage_'.$name} ?? $value;

            $meta->$name = $value;
        }

        $page = \Mim::$app->req->getQuery('page');
        if($page && $page > 1){
            $meta->title.= ' Page ' . $page;
            $meta->description.= ' Page ' . $page;
        }

        $result['head'] = [
            'description'       => $meta->description,
            'published_time'    => \Mim::$app->config->install,
            'schema.org'        => [],
            'type'              => 'website',
            'title'             => $meta->title,
            'updated_time'      => date('c'),
            'url'               => $home_url,
            'metas'             => []
        ];

        // schema page
        $site = [
            '@context'      => 'http://schema.org',
            '@type'         => 'WebSite',
            'name'          => \Mim::$app->config->name,
            'description'   => $meta->description,
            'headline'      => $meta->description,
            'publisher'     => \Mim::$app->meta->schemaOrg( \Mim::$app->config->name ),
            'url'           => $home_url,
            'image'         => [
                '@type'         => 'ImageObject',
                'url'           => \Mim::$app->router->asset('site', 'logo/192x192.png'),
                'height'        => 192,
                'width'         => 192
            ]
        ];

        // potentialAction
        if(module_exists('site-search')){
            $route = \Mim::$app->router->to('siteSearch', [], ['q'=>'']);
            $site['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => $route . '{search_term_string}',
                'query-input' => 'required name=search_term_string'
            ];
        }

        $result['head']['schema.org'][] = $site;

        return $result;
    }
}
