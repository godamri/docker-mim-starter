<?php
/**
 * Default robot controller
 * @package site
 * @version 0.0.2
 */

namespace Site\Controller;

use LibRobot\Library\Feed;
use LibRobot\Library\Sitemap;

class RobotController extends \Site\Controller
{
    public function feedAction(){
        $handlers = (array)$this->config->site->robot->feed;
        $links = [];
        foreach($handlers as $handler => $proc){
            if(!$proc)
                continue;
            
            $handler = explode('::', $handler);
            $class   = $handler[0];
            $method  = $handler[1];

            $nlnk    = $class::$method();
            if($nlnk)
                $links = array_merge($links, $nlnk);
        }

        $feed_opts = (object)[
            'self_url'          => $this->router->to('siteFeed'),
            'copyright_year'    => date('Y'),
            'copyright_name'    => \Mim::$app->config->name,
            'description'       => '...',
            'language'          => 'id-ID',
            'host'              => $this->router->to('siteHome'),
            'title'             => \Mim::$app->config->name
        ];

        Feed::render($links, $feed_opts);
        $this->res->setCache(3600);
        $this->res->send();
    }

    public function sitemapAction(){
        $handlers = (array)$this->config->site->robot->sitemap;
        $links = [];

        foreach($handlers as $handler => $proc){
            if(!$proc)
                continue;

            $handler = explode('::', $handler);
            $class   = $handler[0];
            $method  = $handler[1];

            $nlnk    = $class::$method();
            if($nlnk)
                $links = array_merge($links, $nlnk);
        }

        Sitemap::render($links);
        $this->res->setCache(3600);
        $this->res->send();
    }
}