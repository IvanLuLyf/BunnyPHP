<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/28
 * Time: 15:31
 */
class IndexController extends Controller
{
    public function index()
    {
        $user = $this->filter("Auth");
        if ($user != null) {
            header('Location: /post/index');
        } else {
            header('Location: /user/login');
        }
    }
}