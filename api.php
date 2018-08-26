<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/23
 * Time: 13:46
 */

define('APP_PATH', __DIR__ . '/');
define('APP_DEBUG', true);
define("IN_TWIMI_PHP", "True", TRUE);
date_default_timezone_set('PRC');
require(APP_PATH . 'BunnyPHP/BunnyPHP.php');
if (file_exists("config/config.php")) {
    $config = require(APP_PATH . 'config/config.php');
} else {
    $config = [
        'controller' => 'Index',
        'action' => 'index'
    ];
}
(new BunnyPHP($config, BunnyPHP::MODE_API))->run();