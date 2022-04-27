<?php
/**
 * User provider
 * @package lib-user-main
 * @version 0.0.1
 */

namespace LibUserMain\Handler;

use LibUserMain\Model\User as _User;

class User implements \LibUser\Iface\Handler
{
    static function getByCredentials(string $identity, string $password, array $where=[]): ?object{
        $cond = [];
        $bys = \Mim::$app->config->libUserMain->login->by;
        foreach($bys as $by => $allow){
            if($allow)
                $cond[] = [$by => $identity];
        }

        if(!$cond)
            return null;

        $where['$or'] = $cond;
        $user = _User::getOne($where);
        if(!$user)
            return null;
        if($user->status == 0)
            return null;

        if(!self::verifyPassword($password, $user))
            return null;
        return $user;
    }

    static function count(array $where=[]): int{
        return _User::count($where);
    }

    static function getById(string $identity): ?object{
        $user = _User::getOne(['id'=>$identity]);
        return $user ? $user : null;
    }

    static function getMany(array $where, int $rpp=0, int $page=1, array $order=[]): ?array{
        return _User::get($where, $rpp, $page, $order);
    }

    static function getOne(array $where): ?object{
        return _User::getOne($where);
    }

    static function hashPassword(string $password): ?string{
        return password_hash($password, PASSWORD_DEFAULT);
    }

    static function lastError(){
        return _User::lastError();
    }

    static function verifyPassword(string $password, object $user): bool{
        return password_verify($password, $user->password);
    }

    static function set(array $fields, array $where=[]): bool{
        return _User::set($fields, $where);
    }
}