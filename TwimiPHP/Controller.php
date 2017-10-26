<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 15:58
 */
class Controller
{
    protected $_controller;
    protected $_action;
    protected $_view;

    public function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_view = new View($controller, $action);
    }

    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }

    public function render()
    {
        $this->_view->render();
    }

    public function filter($filterName)
    {
        $filter = $filterName . 'Filter';
        if (!class_exists($filter)) {
            exit($filter . ' Not Found');
        }
        $dispatch = new $filter();
        return call_user_func(array($dispatch, "doFilter"));
    }
}