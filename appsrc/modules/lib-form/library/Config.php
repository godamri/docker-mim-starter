<?php
/**
 * Config
 * @package lib-form
 * @version 0.3.0
 */

namespace LibForm\Library;


class Config
{
    private static function fetchFormExtends(array &$forms, string $name): void{
        $form    = $forms[$name];
        if(!isset($form['@extends']))
            return;
        
        $extends = $form['@extends'];

        unset($forms[$name]['@extends']);
        unset($form['@extends']);

        $new_form = [];
        foreach($extends as $extend){
            $ext_form = $forms[$extend];
            if(isset($ext_form['@extends']))
                self::fetchFormExtends($forms, $extend);

            $new_form = array_replace_recursive($new_form, $forms[$extend]);
        }

        $result = array_replace_recursive($new_form, $form);

        $forms[$name] = $result;
    }

    private static function parseExtends(object $forms): object{
        $forms = arrayfy($forms);
        foreach($forms as $name => $form){
            if(!isset($form['@extends']))
                continue;
            self::fetchFormExtends($forms, $name);
        }

        return objectify($forms);
    }

    static function reconfig(object &$configs, string $here) {
        $fConfig = $configs->libForm ?? NULL;
        if(!$fConfig)
            return;

        $configs->libForm->forms = self::parseExtends($fConfig->forms);
    }
}