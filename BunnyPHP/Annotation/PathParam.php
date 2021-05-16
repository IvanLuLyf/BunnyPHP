<?php

namespace BunnyPHP\Annotation;

use Attribute;
use BunnyPHP\BaseParam;
use BunnyPHP\BunnyPHP;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PathParam extends BaseParam
{
    public function value()
    {
        $path = BunnyPHP::getPath();
        if ($this->name === '') return $path;
        return $path[$this->name] ?? $this->defaultVal;
    }
}