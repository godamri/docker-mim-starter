<?php
/**
 * Lib locale helper
 * @package lib-locale
 * @version 0.0.1
 */

function lang(string $key, array $params=[], string $locale=null): string{
    return \LibLocale\Library\Locale::translate($key, $params, $locale);
}