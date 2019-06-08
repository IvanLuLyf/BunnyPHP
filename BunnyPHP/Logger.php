<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/4/19
 * Time: 22:27
 */

namespace BunnyPHP;

interface Logger
{
    public function info($message, array $context = []);

    public function error($message, array $context = []);

    public function warn($message, array $context = []);

    public function debug($message, array $context = []);
}