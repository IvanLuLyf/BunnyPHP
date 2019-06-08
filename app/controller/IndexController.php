<?php

use BunnyPHP\Controller;

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/8/12
 * Time: 1:05
 */
class IndexController extends Controller
{
    function ac_index()
    {
        $this->render("index.html");
    }
}