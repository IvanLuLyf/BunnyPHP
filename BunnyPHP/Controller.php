<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 18:03
 */

class Controller
{
    protected $_controller;
    protected $_action;
    protected $_view;
    protected $_mode;
    protected $_storage;

    public function __construct($controller, $action, $mode = BunnyPHP::MODE_NORMAL)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_view = new View($controller, $action, $mode);
        $this->_mode = $mode;
    }

    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }

    public function assignAll($arr)
    {
        $this->_view->assignAll($arr);
    }

    public function render($template = '')
    {
        $this->_view->render($template);
    }

    public function redirect($url)
    {
        $this->_view->redirect($url);
    }

    public function service($serviceName): Service
    {
        $service = ucfirst($serviceName) . 'Service';
        return new $service;
    }

    public function storage(): Storage
    {
        return BunnyPHP::getStorage();
    }
}