<?php
declare(strict_types=1);

namespace BunnyPHP;
interface Logger
{
    public function info($message, array $context = []);

    public function error($message, array $context = []);

    public function warn($message, array $context = []);

    public function debug($message, array $context = []);
}