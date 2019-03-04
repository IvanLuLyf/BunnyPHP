<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/8/4
 * Time: 18:38
 */

interface Storage
{
    public function read($filename);

    public function write($filename, $content);

    public function upload($filename, $path);

    public function remove($filename);
}