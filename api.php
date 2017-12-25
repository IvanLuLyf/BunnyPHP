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
require(APP_PATH . 'TwimiPHP/TwimiPHP.php');
$config = require(APP_PATH . 'config/config.php');
(new TwimiPHP($config,1))->run();