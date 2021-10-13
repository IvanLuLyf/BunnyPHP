<?php

namespace BunnyPHP\Annotation;

use Attribute;
use BunnyPHP\BaseParam;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestParam extends BaseParam
{
    public function value()
    {
        return $_REQUEST[$this->name] ?? $this->defaultVal;
    }
}