<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/23
 * Time: 13:46
 */

header('X-Powered-By:BunnyFramework');
define('APP_PATH', __DIR__ . '/');
define('APP_DEBUG', true);
define("IN_TWIMI_PHP", "True", TRUE);
date_default_timezone_set('PRC');
require(APP_PATH . 'BunnyPHP/BunnyPHP.php');
(new BunnyPHP(BunnyPHP::MODE_API))->run();