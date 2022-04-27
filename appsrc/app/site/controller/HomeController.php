<?php
/**
 * Default home controller
 * @package site
 * @version 0.0.1
 */

namespace Site\Controller;

use Site\Library\Meta;

class HomeController extends \Site\Controller
{
    public function indexAction(){
        $params = [
            'meta'  => Meta::single()
        ];

        $this->res->render('home/index', $params);
        $this->res->setCache(86400);
        $this->res->send();
    }
}