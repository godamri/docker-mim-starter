<?php
/**
 * Authorizer
 * @package lib-user
 * @version 0.0.1
 */

namespace LibUser\Iface;

interface Authorizer
{

    static function getSession(): ?object;
    
    static function identify(): ?string;

    static function loginById(string $identity): ?array;

    static function logout(): void;
}