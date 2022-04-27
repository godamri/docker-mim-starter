<?php
/**
 * Setting
 * @package site-setting
 * @version 1.1.1
 */

namespace SiteSetting\Service;

use SiteSetting\Model\SiteSetting as SSetting;

class Setting extends \Mim\Service
{

    private $settings;

    public function __construct(){
        $fetch = false;
        $cache_exists = module_exists('lib-cache');
        $cache_name   = 'site-setting';
        $mim = \Mim::$app;

        if ($cache_exists) {
            $this->settings = $mim->cache->get($cache_name);
            if ($this->settings) {
                return;
            }
        }

        $rows = SSetting::get();
        if(!$rows)
            return;

        $result = (object)[];

        foreach($rows as $row){
            $value = $row->value;
            switch ($row->type) {
                case '3':
                    $value = (int)$value;
                    break;

                case '4':
                    $value = (bool)$value;
                    break;
            }
            $result->{$row->name} = $value;
        }

        if($cache_exists)
            $mim->cache->add($cache_name, $result, (60*60*24));
        $this->settings = $result;
    }

    public function __get($name) {
        if(!$this->settings)
            return null;
        return $this->settings->$name ?? null;
    }
}
