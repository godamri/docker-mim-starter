<?php
/**
 * PostController
 * @package site-post
 * @version 0.0.1
 */

namespace SitePost\Controller;

use SitePost\Library\Meta;
use Post\Model\Post;
use LibFormatter\Library\Formatter;

class PostController extends \Site\Controller
{
    public function singleAction() {
        $slug = $this->req->param->slug;

        $post = Post::getOne(['slug'=>$slug, 'status'=>3]);
        if(!$post)
            return $this->show404();

        $post = Formatter::format('post', $post, ['user', 'content']);

        $params = [
            'post' => $post,
            'meta' => Meta::single($post)
        ];

        $this->res->render('post/single', $params);
        $this->res->setCache(86400);
        $this->res->send();
    }
}