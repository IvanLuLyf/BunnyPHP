<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:06
 */

class View
{
    public static function render($view, $context = [], $mode = BunnyPHP::MODE_NORMAL)
    {
        if ($mode == BunnyPHP::MODE_API or $mode == BunnyPHP::MODE_AJAX) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($context);
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            extract($context);
            if (file_exists(APP_PATH . "template/{$view}")) {
                include APP_PATH . "template/{$view}";
            } elseif (file_exists(APP_PATH . "app/view/{$view}")) {
                include APP_PATH . "app/view/{$view}";
            } else if ($view == '' || $view == null) {
                echo json_encode($context);
            } else {
                self::error(['ret' => '-3', 'status' => 'template not exists', 'tp_error_msg' => "模板${view}不存在"]);
            }
        }
    }

    public static function error($context = [], $mode = BunnyPHP::MODE_NORMAL)
    {
        if ($mode == BunnyPHP::MODE_API or $mode == BunnyPHP::MODE_AJAX) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($context);
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            $error_html = "<html><head><title>BunnyPHP Error</title></head><body><h2>BunnyPHP Error</h2><p>{$context['tp_error_msg']}</p></body></html>";
            echo $error_html;
        }
        exit();
    }

    public static function get_url($mod, $action, $params = [])
    {
        $query = http_build_query($params);
        if ($query != '') $query = '?' . $query;
        return "/${mod}/${action}${query}";
    }

    public static function redirect($url, $action = null, $params = [])
    {
        if ($action == null) {
            header("Location: $url");
        } else {
            $query = http_build_query($params);
            if ($query != '') $query = '?' . $query;
            header("Location: /${url}/${action}${query}");
        }
    }
}