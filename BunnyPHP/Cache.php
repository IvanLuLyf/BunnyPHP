<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/31
 * Time: 14:07
 */

interface Cache
{
    public function get($key);

    public function has($key);

    public function set($key, $value);

    public function del($key);
}