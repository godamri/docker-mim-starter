<?php
/**
 * Form
 * @package lib-form
 * @version 0.0.1
 */

namespace LibForm\Service;

use LibForm\Library\FormCollection;

class Form extends \Mim\Service
{
    public function __get($name){
        return FormCollection::getForm($name);
    }

    public function __call($name, $args){
        return FormCollection::__callStatic($name, $args);
    }
}