<?php
/**
 * View handler
 * @package lib-view
 * @version 0.0.1
 */

namespace LibView\Library;

class View
{
    static function render(string $view, array $params=[], string $gate=null): ?string{
        $conf = \Mim::$app->config->libView;
        if(!$gate)
            $gate = \Mim::$app->req->gate->name;
        
        $renderer = $conf->renderer;
        $handlers = $conf->handlers->$renderer;

        $handler = new $handlers();

        return $handler->render($view, $params, $gate);
    }
}