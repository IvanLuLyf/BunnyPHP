<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 15:58
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;

    function __construct($controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
    }

    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function render()
    {
        extract($this->variables);
        $defaultHeader = APP_PATH . 'app/views/header.php';
        $defaultFooter = APP_PATH . 'app/views/footer.php';
        $controllerHeader = APP_PATH . 'app/views/' . $this->_controller . '/header.php';
        $controllerFooter = APP_PATH . 'app/views/' . $this->_controller . '/footer.php';
        $controllerLayout = APP_PATH . 'app/views/' . $this->_controller . '/' . $this->_action . '.php';

        if (file_exists($controllerHeader)) {
            include($controllerHeader);
        } else {
            include($defaultHeader);
        }

        if (file_exists($controllerLayout)) {
            include($controllerLayout);
        } else {
            echo "<h1>无法找到视图文件</h1>";
        }

        if (file_exists($controllerFooter)) {
            include($controllerFooter);
        } else {
            include($defaultFooter);
        }
    }
}