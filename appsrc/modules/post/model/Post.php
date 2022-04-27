<?php
/**
 * Post
 * @package post
 * @version 0.0.1
 */

namespace Post\Model;

class Post extends \Mim\Model
{

    protected static $table = 'post';

    protected static $chains = [];

    protected static $q = ['title'];
}