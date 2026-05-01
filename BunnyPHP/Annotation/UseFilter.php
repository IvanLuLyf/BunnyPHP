<?php
declare(strict_types=1);

namespace BunnyPHP\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class UseFilter
{
    public string $name;
    public array $args;

    public function __construct(string $name, ...$args)
    {
        $this->name = $name;
        $this->args = $args;
    }
}

