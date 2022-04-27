<?php
/**
 * Event
 * @package site-setting
 * @version 1.0.0
 */

namespace SiteSetting\Library;


class Event
{
    static function clear(object $item): void{
        \Mim::$app->cache->remove('site-setting');
    }
}