<?php
declare(strict_types=1);

namespace BunnyPHP;
class Controller
{
    protected array $_variables = [];

    public function __get($name)
    {
        if ($name === '_mode') return BUNNY_APP_MODE;
        else if ($name === '_controller') return BUNNY_CONTROLLER;
        else if ($name === '_action') return BUNNY_ACTION;
        return null;
    }

    public function getController(): string
    {
        return BUNNY_CONTROLLER;
    }

    public function getAction(): string
    {
        return BUNNY_ACTION;
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
        View::render($template, $this->_variables, BUNNY_APP_MODE, $code);
    }

    public function renderTemplate($template = '', $code = 200)
    {
        Template::render($template, $this->_variables, BUNNY_APP_MODE, $code);
    }

    public function error($code = 200)
    {
        View::error($this->_variables, BUNNY_APP_MODE, $code);
    }

    public function info($code = 200)
    {
        View::info($this->_variables, BUNNY_APP_MODE, $code);
    }

    public function redirect($url, $action = null, $params = [])
    {
        View::redirect($url, $action, $params);
    }
}