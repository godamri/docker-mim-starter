<?php
/**
 * Formatter interface
 * @package lib-formatter
 * @version 0.0.1
 */

namespace LibFormatter\Iface;

interface Formatter
{
    static function format(string $format, object $object, array $options=[]): ?object;
    static function formatMany(string $format, array $objects, array $options=[], string $askey=null): ?array;
}