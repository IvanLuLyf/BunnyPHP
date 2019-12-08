<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:06
 */

namespace BunnyPHP;

class View
{
    const MODE_INFO = 1;
    const MODE_ERROR = 2;

    public static function render($view, $context = [], $mode = BunnyPHP::MODE_NORMAL, $code = 200)
    {
        if ($code !== 200) {
            http_response_code($code);
        }
        if ($mode === BunnyPHP::MODE_API or $mode === BunnyPHP::MODE_AJAX) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($context);
        } elseif ($mode === BunnyPHP::MODE_CLI) {
            echo self::get_message($context);
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            if (is_string($view)) {
                if (!empty($view) && file_exists(APP_PATH . "template/{$view}")) {
                    extract($context);
                    include APP_PATH . "template/{$view}";
                } else if (empty($view)) {
                    echo self::get_message($context);
                } else {
                    self::error(['ret' => '-4', 'status' => 'template does not exist', 'tp_error_msg' => Language::get('view_not_exists', ['view' => $view])]);
                }
            } elseif (is_array($view)) {
                if (!empty($view[0]) && is_dir($view[1]) && file_exists($view[1] . "/{$view[0]}")) {
                    extract($context);
                    include $view[1] . "/{$view[0]}";
                } else {
                    self::error(['ret' => '-4', 'status' => 'template does not exist', 'tp_error_msg' => Language::get('view_not_exists', ['view' => $view])]);
                }
            } elseif ($view === self::MODE_ERROR) {
                if (file_exists("template/error.html")) {
                    extract($context);
                    include APP_PATH . "template/error.html";
                } else {
                    echo Language::get('bunny_error', ['error' => self::get_message($context)]);
                }
            } elseif ($view === self::MODE_INFO) {
                if (file_exists("template/info.html")) {
                    extract($context);
                    include APP_PATH . "template/info.html";
                } else {
                    echo Language::get('bunny_info', ['info' => self::get_message($context)]);
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

    private static function get_message($context)
    {
        if (isset($context['tp_error_msg'])) {
            return $context['tp_error_msg'];
        } elseif (isset($context['tp_info_msg'])) {
            return $context['tp_info_msg'];
        } elseif (isset($context['response'])) {
            return $context['response'];
        } else {
            return '';
        }
    }

    public static function get_url($mod, $action, $params = [])
    {
        $query = http_build_query($params);
        if (TP_SITE_REWRITE === true) {
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
            header('Location: ' . self::get_url($url, $action, $params));
        }
    }
}