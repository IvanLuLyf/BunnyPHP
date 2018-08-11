<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:06
 */

class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;
    protected $_mode;

    function __construct($controller, $action, $mode = BunnyPHP::MODE_NORMAL)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
        $this->_mode = $mode;
    }

    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function assignAll($arr)
    {
        $this->variables = array_merge($this->variables, $arr);
    }

    public function render($template)
    {
        if ($this->_mode == BunnyPHP::MODE_NORMAL) {
            extract($this->variables);
            $view = APP_PATH . 'app/view/' . $template;
            $temp = APP_PATH . 'template/' . $template;
            if (file_exists($temp)) {
                include($temp);
            } elseif (file_exists($view)) {
                include($view);
            } else {
                echo "<h1>View Not Found</h1>";
            }
        } elseif ($this->_mode == BunnyPHP::MODE_API) {
            echo json_encode($this->variables);
        }
    }

    public function redirect($url)
    {
        header("Location: " . $url);
    }
}