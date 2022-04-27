<?php
/**
 * User
 * @package lib-user-main
 * @version 0.0.1
 */

namespace LibUserMain\Model;

class User extends \Mim\Model
{

    protected static $table = 'user';

    protected static $chains = [];

    protected static $q = ['name','fullname'];
}