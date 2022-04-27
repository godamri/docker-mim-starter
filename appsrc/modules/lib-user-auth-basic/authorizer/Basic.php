<?php
/**
 * User basic authorizer
 * @package lib-user-auth-basic
 * @version 0.0.1
 */

namespace LibUserAuthBasic\Authorizer;

use LibEvent\Library\Event;

class Basic implements \LibUser\Iface\Authorizer
{
    private static $session;

    static function getSession(): ?object{
        return self::$session;
    }
    
    static function identify(): ?string{
        $uname = $_SERVER['PHP_AUTH_USER'] ?? null;
        if(!$uname)
            return null;
        $upass = $_SERVER['PHP_AUTH_PW'];

        self::$session = (object)[
            'type' => 'basic',
            'expires' => 0,
            'token' => preg_replace('!^Basic !', '', $_SERVER['HTTP_AUTHORIZATION'])
        ];

        $handler = \Mim::$app->config->libUser->handler;
        $user = $handler::getByCredentials($uname, $upass);
        if(!$user)
            return null;
        self::$session->user_id = $user->id;
        return $user->id;
    }

    static function loginById(string $identity): ?array{
        if(module_exists('lib-event'))
            Event::trigger('user:authorized', $identity);
        return null;
    }

    static function logout(): void{
        return;
    }
}