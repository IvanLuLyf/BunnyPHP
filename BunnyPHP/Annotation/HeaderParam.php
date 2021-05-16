<?php

namespace BunnyPHP\Annotation;

use Attribute;
use BunnyPHP\BaseParam;
use BunnyPHP\BunnyPHP;

#[Attribute(Attribute::TARGET_PARAMETER)]
class HeaderParam extends BaseParam
{
    function value()
    {
        return BunnyPHP::getRequest()->getHeader($this->name) ?? $this->defaultVal;
    }
}