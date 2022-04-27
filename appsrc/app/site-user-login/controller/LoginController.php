<?php
/**
 * LoginController
 * @package site-user-login
 * @version 0.0.1
 */

namespace SiteUserLogin\Controller;

use SiteUserLogin\Library\Meta;
use LibForm\Library\Form;
use LibUserAuthCookie\Authorizer\Cookie;

class LoginController extends \Site\Controller
{
    public function logoutAction(){
        $session = $this->user->getSession();
        if($session)
            $this->user->logout();

        $next = $this->router->to('siteHome');
        $this->res->redirect($next);
    }

    public function loginAction() {
        $next = $this->req->getQuery('next');
        if(!$next)
            $next = $this->router->to('siteHome');

        if($this->user->isLogin())
            return $this->res->redirect($next);

        $form = new Form('site.me.login');

        $params = [
            '_meta' => [
                'title' => 'Login'
            ],
            'error' => false,
            'form'  => $form,
            'meta' => Meta::single()
        ];

        if(!($valid = $form->validate()) || !$form->csrfTest('noob')){
            $this->res->render('me/login', $params);
            return $this->res->send();
        }

        $user = $this->user->getByCredentials($valid->name, $valid->password);
        if(!$user){
            $params['error'] = true;
            $this->res->render('me/login', $params);
            return $this->res->send();
        }
        
        $keep = $this->req->getPost('keep');
        if(!$keep)
            Cookie::setKeep(false);

        Cookie::loginById($user->id);

        $this->res->redirect($next);
    }
}