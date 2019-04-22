<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:06
 */

class View
{
    const MODE_INFO = 1;
    const MODE_ERROR = 2;

    public static function render($view, $context = [], $mode = BunnyPHP::MODE_NORMAL, $code = 200)
    {
        if ($code !== 200) {
            http_send_status($code);
        }
        if ($mode === BunnyPHP::MODE_API or $mode === BunnyPHP::MODE_AJAX) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($context, JSON_NUMERIC_CHECK);
        } elseif ($mode === BunnyPHP::MODE_CLI) {
            if (isset($context['response'])) {
                echo $context['response'];
            } elseif (isset($context['tp_error_msg'])) {
                echo $context['tp_error_msg'];
            } else {
                print_r($context);
            }
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            if (is_string($view)) {
                extract($context);
                if (!empty($view) && file_exists(APP_PATH . "template/{$view}")) {
                    include APP_PATH . "template/{$view}";
                } else if (empty($view)) {
                    if (isset($context['response'])) {
                        echo $context['response'];
                    }
                } else {
                    self::error(['ret' => '-4', 'status' => 'template does not exist', 'tp_error_msg' => "模板${view}不存在"]);
                }
            } elseif ($view === self::MODE_ERROR) {
                header("Content-Type: text/html; charset=UTF-8");
                if (file_exists("template/error.html")) {
                    extract($context);
                    include APP_PATH . "template/error.html";
                } else {
                    $error_html = "<html><head><title>BunnyPHP Error</title></head><body><h2>BunnyPHP Error</h2><p>{$context['tp_error_msg']}</p></body></html>";
                    echo $error_html;
                }
            } elseif ($view === self::MODE_INFO) {
                header("Content-Type: text/html; charset=UTF-8");
                if (file_exists("template/info.html")) {
                    extract($context);
                    include APP_PATH . "template/info.html";
                } else {
                    $info_html = "<html><head><title>BunnyPHP Info</title></head><body><h2>BunnyPHP Info</h2><p>{$context['tp_info_msg']}</p></body></html>";
                    echo $info_html;
                }
            }
        }
    }

    public static function error($context = [], $mode = BunnyPHP::MODE_NORMAL, $code = 200)
    {
        self::render(self::MODE_ERROR, $context, $mode, $code);
        exit();
    }

    public static function info($context = [], $mode = BunnyPHP::MODE_NORMAL, $code = 200)
    {
        self::render(self::MODE_INFO, $context, $mode, $code);
        exit();
    }

    public static function get_url($mod, $action, $params = [])
    {
        $query = http_build_query($params);
        if (constant('TP_SITE_REWRITE') == true) {
            if ($query != '') $query = '?' . $query;
            return "/${mod}/${action}${query}";
        } else {
            if ($query != '') $query = '&' . $query;
            return "/index.php?mod=${mod}&action=${action}${query}";
        }
    }

    public static function redirect($url, $action = null, $params = [])
    {
        if ($action == null) {
            header("Location: $url");
        } else {
            $query = http_build_query($params);
            if (constant('TP_SITE_REWRITE') == true) {
                if ($query != '') $query = '?' . $query;
                header("Location: /${url}/${action}${query}");
            } else {
                if ($query != '') $query = '&' . $query;
                header("Location: /index.php?mod=${url}&action=${action}${query}");
            }
        }
    }
}