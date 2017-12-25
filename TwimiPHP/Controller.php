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
    protected $_mode;

    public function __construct($controller, $action, $mode = 0)
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

    public function render($action = null, $useHeader = true, $useFooter = true)
    {
        $this->_view->render($action, $useHeader, $useFooter);
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

    public function storage($storageName = TP_STORAGE)
    {
        $storage = $storageName . 'Storage';
        if (!class_exists($storage)) {
            exit($storageName . ' Not Found');
        }
        return new $storage();
    }
}