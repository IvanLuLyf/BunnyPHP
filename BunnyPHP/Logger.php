<?php
declare(strict_types=1);

namespace BunnyPHP;
interface Logger
{
    const INFO = 'INFO';
    const ERROR = 'ERROR';
    const WARN = 'WARN';
    const DEBUG = 'DEBUG';

    public function info($message, array $context = []);

    public function error($message, array $context = []);

    public function warn($message, array $context = []);

    public function debug($message, array $context = []);
}