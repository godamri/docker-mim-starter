<?php
/**
 * App starter
 * @package core
 * @version 1.8.0
 */

if (!defined('BASEPATH'))
    define('BASEPATH', __DIR__);

require_once BASEPATH . '/modules/core/Mim.php';
return Mim::init();
