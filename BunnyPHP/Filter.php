<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/9/29
 * Time: 13:59
 */

class Filter
{
    const NEXT = 0;
    const STOP = 1;

    protected $_mode;

    public function __construct($mode = BunnyPHP::MODE_NORMAL)
    {
        $this->_mode = $mode;
    }

    public function doFilter()
    {
        return self::NEXT;
    }

    public function render($variable, $template = '')
    {
        View::render($template, $variable, $this->_mode);
    }

    public function error($variable)
    {
        View::error($variable, $this->_mode);
    }

    public function redirect($url, $action = null, $params = [])
    {
        View::redirect($url, $action, $params);
    }
}