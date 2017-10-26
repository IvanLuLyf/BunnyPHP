<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/26
 * Time: 17:22
 */
class AuthFilter extends Filter
{
    public function doFilter()
    {
        session_start();
        if (isset($_SESSION['token']) && $_SESSION['token'] != "") {
            $user = (new UserModel)->where(["token = ? and expire>UNIX_TIMESTAMP()"], [$_SESSION["token"]])->fetch();
            return $user;
        } else {
            return null;
        }
    }
}