<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/31
 * Time: 14:07
 */

interface Cache
{
    public function get($key, $expire = 0);

    public function has($key, $expire = 0);

    public function set($key, $value, $expire = 0);

    public function del($key);
}