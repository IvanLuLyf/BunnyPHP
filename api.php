<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/23
 * Time: 13:46
 */
$url = $_SERVER['REQUEST_URI'];
echo ($url."<br>");
echo ($_GET['mod']."<br>");
echo ($_GET['action']."<br>");
echo ($_SESSION['token']."<br>");