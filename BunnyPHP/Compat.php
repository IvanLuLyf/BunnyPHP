<?php
declare(strict_types=1);

namespace BunnyPHP;

final class Compat
{
    public static function supportsUnionTypes(): bool
    {
        // PHP 8.0+ supports union types and ReflectionUnionType
        return PHP_VERSION_ID >= 80000 && class_exists('\\ReflectionUnionType', false);
    }

    public static function isUnionType($reflectionType): bool
    {
        return self::supportsUnionTypes() && $reflectionType instanceof \ReflectionUnionType;
    }
}

