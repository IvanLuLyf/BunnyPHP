<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/26
 * Time: 18:01
 */
class UserController extends Controller
{
    public function index()
    {
        $user = $this->filter("Auth");
        if ($user != null) {
            header('Location: /index/index');
        } else {
            header('Location: /user/login');
        }
    }

    public function login()
    {
        $this->assign("title","登陆");
        if (isset($_POST['username'])) {
            $user = new UserModel;
            $username = $_POST['username'];
            $password = $_POST['password'];

            $row = $user->where(["email = ? or username = ?"], [$username, $username])->fetch();
            if ($row != null) {
                if ($row['password'] == md5($password)) {
                    $timeline = time();
                    if ($timeline > $row['expire']) {
                        $token = md5($row['uid'] . $row['username'] . $timeline);
                        $update = array('token' => $token, 'expire' => $timeline + 604800);
                        $user->where(["uid = ?"], [$row['uid']])->update($update);
                    } else {
                        $token = $row['token'];
                    }
                    setcookie('token', $token, 604800);
                    header('Location: /index/index');
                } else {
                    $this->assign('tp_error_msg', '密码错误');
                    $this->render();
                }
            } else {
                $this->assign('tp_error_msg', '用户名不存在');
                $this->render();
            }
        } else {
            $this->render();
        }
    }
}