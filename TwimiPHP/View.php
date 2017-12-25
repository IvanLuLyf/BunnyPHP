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
    protected $_mode;

    function __construct($controller, $action, $mode = 0)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
        $this->_mode = $mode;
    }

    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function render($action = null, $useHeader = true, $useFooter = true)
    {
        if ($this->_mode == 0) {
            extract($this->variables);
            $defaultHeader = APP_PATH . 'app/views/header.php';
            $defaultFooter = APP_PATH . 'app/views/footer.php';
            $controllerHeader = APP_PATH . 'app/views/' . $this->_controller . '/header.php';
            $controllerFooter = APP_PATH . 'app/views/' . $this->_controller . '/footer.php';

            if ($action == null) {
                $controllerLayout = APP_PATH . 'app/views/' . $this->_controller . '/' . $this->_action . '.php';
            } else {
                $controllerLayout = APP_PATH . 'app/views/' . $this->_controller . '/' . $action . '.php';
            }

            if ($useHeader) {
                if (file_exists($controllerHeader)) {
                    include($controllerHeader);
                } else if (file_exists($defaultHeader)) {
                    include($defaultHeader);
                }
            }

            if (file_exists($controllerLayout)) {
                include($controllerLayout);
            } else {
                echo "<h1>无法找到视图文件</h1>";
            }

            if ($useFooter) {
                if (file_exists($controllerFooter)) {
                    include($controllerFooter);
                } else if (file_exists($defaultFooter)) {
                    include($defaultFooter);
                }
            }
        } elseif ($this->_mode == 1) {
            echo json_encode($this->variables);
        }
    }
}