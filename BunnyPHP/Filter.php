<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/9/29
 * Time: 13:59
 */

namespace BunnyPHP;

class Filter
{
    const NEXT = 0;
    const STOP = 1;

    protected $_mode;
    protected $_variables = [];

    public function __construct($mode = BunnyPHP::MODE_NORMAL)
    {
        $this->_mode = $mode;
    }

    public function doFilter($param = [])
    {
        return self::NEXT;
    }

    public function assign($name, $value)
    {
        $this->_variables[$name] = $value;
    }

    public function getVariable(): array
    {
        return $this->_variables;
    }

    public function render($variable, $template = '')
    {
        View::render($template, $variable, $this->_mode);
    }

    public function error($variable, $code = 200)
    {
        View::error($variable, $this->_mode, $code);
    }

    public function redirect($url, $action = null, $params = [])
    {
        View::redirect($url, $action, $params);
    }
}