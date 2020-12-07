<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:03
 */

namespace BunnyPHP;
class Controller
{
    protected $_variables = [];
    protected $_controller;
    protected $_action;
    protected $_mode;

    public function __construct($controller, $action, $mode = BunnyPHP::MODE_NORMAL)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_mode = $mode;
    }

    public function getController(): string
    {
        return $this->_controller;
    }

    public function getAction(): string
    {
        return $this->_action;
    }

    public function assign($name, $value): Controller
    {
        $this->_variables[$name] = $value;
        return $this;
    }

    public function assignAll($arr): Controller
    {
        $this->_variables = array_merge($this->_variables, $arr);
        return $this;
    }

    public function render($template = '', $code = 200)
    {
        View::render($template, $this->_variables, $this->_mode, $code);
    }

    public function renderTemplate($template = '', $code = 200)
    {
        Template::render($template, $this->_variables, $this->_mode, $code);
    }

    public function error($code = 200)
    {
        View::error($this->_variables, $this->_mode, $code);
    }

    public function info($code = 200)
    {
        View::info($this->_variables, $this->_mode, $code);
    }

    public function redirect($url, $action = null, $params = [])
    {
        View::redirect($url, $action, $params);
    }
}