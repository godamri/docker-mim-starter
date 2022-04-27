<?php
/**
 * FormCollection
 * @package lib-form
 * @version 0.0.1
 */

namespace LibForm\Library;

class FormCollection
{

    private static $forms = [];

    static function getForm($name): object{
        if(!isset(self::$forms[$name]))
            self::$forms[$name] = new Form($name);
        return self::$forms[$name];
    }

    static function __callStatic($name, $args) {
        $form_name = array_shift($args);
        $form = self::getForm($form_name);
        return call_user_func_array([$form, $name], $args);
    }
}