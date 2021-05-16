<?php

namespace BunnyPHP\Annotation;
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestParam extends BaseParam
{
    public function value()
    {
        return $_REQUEST[$this->name] ?? $this->defaultVal;
    }
}