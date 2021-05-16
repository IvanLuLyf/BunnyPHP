<?php
declare(strict_types=1);

namespace BunnyPHP;
class Filter
{
    const NEXT = 0;
    const STOP = 1;

    protected array $_variables = [];

    public function __get($name)
    {
        if ($name === '_mode') return BUNNY_APP_MODE;
        return null;
    }

    public function doFilter($param = []): int
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
        View::render($template, $variable, BUNNY_APP_MODE);
    }

    public function error($variable, $code = 200)
    {
        View::error($variable, BUNNY_APP_MODE, $code);
    }

    public function redirect($url, $action = null, $params = [])
    {
        View::redirect($url, $action, $params);
    }
}