<?php

namespace BunnyPHP;
abstract class BaseParam
{
    protected mixed $name;
    protected mixed $defaultVal;

    public function __construct($name = '', $defVal = '')
    {
        $this->name = $name;
        $this->defaultVal = $defVal;
    }

    abstract function value();
}