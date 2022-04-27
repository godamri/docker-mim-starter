<?php
/**
 * User
 * @package lib-user
 * @version 0.0.1
 */

namespace LibUser\Service;

use LibEvent\Library\Event;

class User extends \Mim\Service
{

    private $authorizer;
    private $handler;
    private $_user;
    private $_locale = 'en-US';

    public function __construct(){
        $languages = \Mim::$app->req->accept->language;
        foreach($languages as $lang){
            if(preg_match('![a-z]{2}-[A-Z]{2}!', $lang)){
                $this->_locale = $lang;
                break;
            }
        }

        // find the handler
        $config = \Mim::$app->config->libUser;
        $handler = $config->handler;
        if(!$handler)
            trigger_error('No user handler registered');
        $this->handler = $handler;

        $authorizers = $config->authorizers;
        if(!$authorizers)
            trigger_error('No user authorizer registered');
        
        foreach($authorizers as $name => $class){
            $identity = $class::identify();
            if(!$class::getSession())
                continue;
            $this->authorizer = $class;
            if(!$identity)
                break;
            $user = $this->handler::getById($identity);
            if($user)
                $this->_user = $user;
            break;
        }

        if($this->_user){
            // set timezone if defined
            if(isset($this->_user->timezone)){
                if(in_array($this->_user->timezone, \DateTimeZone::listIdentifiers()))
                    date_default_timezone_set($this->_user->timezone);
            }

            // set locale
            if(isset($this->_user->language) && module_exists('lib-locale')){
                $this->_locale = $this->_user->language;
                \LibLocale\Library\Locale::setLocale($this->_user->language);
            }

            // call event 'user:identified'
            if(module_exists('lib-event'))
                Event::trigger('user:identified', $this->_user);
        }
    }

    public function __get($name) {
        if(!$this->_user)
            return null;
        return $this->_user->$name ?? null;
    }

    public function __isset($name) {
        return isset($this->_user->$name);
    }

    public function getAuthorizer(): ?string{
        return $this->authorizer;
    }

    public function getByCredentials(string $identity, string $password, array $where=[]): ?object {
        if($this->handler)
            return $this->handler::getByCredentials($identity, $password, $where);
        return null;
    }

    public function getById(string $identity): ?object {
        if($this->handler)
            return $this->handler::getById($identity);
        return null;
    }

    public function getHandler(): ?string{
        if($this->handler)
            return $this->handler;
        return null;
    }

    public function getLocale(): string{
        return $this->_locale;
    }

    public function getSession(): ?object{
        if($this->authorizer)
            return $this->authorizer::getSession();
        return null;
    }

    public function getUser(): ?object{
        return $this->_user;
    }

    public function hashPassword(string $password): ?string {
        if($this->handler)
            return $this->handler::hashPassword($password);
        return null;
    }

    public function isLogin(): bool{
        return !!$this->_user;
    }

    public function logout(): void{
        if($this->_user && module_exists('lib-event'))
            Event::trigger('user:identified', $this->_user);

        if($this->authorizer)
            $this->authorizer::logout();
    }

    public function setAuthorizer(string $name){
        $authorizers = \Mim::$app->config->libUser->authorizers;
        if(!isset($authorizers->$name))
            trigger_error('Authorizer with name `' . $name . '` not found');
        $this->authorizer = $authorizers->$name;
    }

    public function setUser(object $user): void{
        $this->user = $user;
    }

    public function verifyPassword(string $password, object $user): bool{
        if($this->handler)
            return $this->handler::verifyPassword($password, $user);
        return false;
    }
}
