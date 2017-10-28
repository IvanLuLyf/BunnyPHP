<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/26
 * Time: 18:01
 */
class UserController extends Controller
{
    public function login()
    {
        if (($user = $this->filter("Auth")) != null) {
            header('Location: /index/index');
        }
        $this->assign("title", "登陆");
        if (isset($_POST['username'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $row = (new UserModel)->where(["email = ? or username = ?"], [$username, $username])->fetch();
            if ($row != null) {
                if ($row['password'] == md5($password)) {
                    $timeline = time();
                    if ($row['expire'] == null || $timeline > intval($row['expire'])) {
                        $uid = $row['id'];
                        $token = md5($row['id'] . $row['username'] . $timeline);
                        $updatedata = array('token' => $token, 'expire' => $timeline + 604800);
                        (new UserModel)->where(["id = :id"], [':id' => $uid])->update($updatedata);
                    } else {
                        $token = $row['token'];
                    }
                    session_start();
                    $_SESSION['token'] = $token;
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

    public function logout()
    {
        session_start();
        unset($_SESSION['token']);
        header('Location: /user/login');
    }
}