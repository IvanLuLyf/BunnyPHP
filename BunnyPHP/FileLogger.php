<?php
declare(strict_types=1);

namespace BunnyPHP;
class FileLogger implements Logger
{
    protected string $filename;

    public function __construct($config)
    {
        $logDir = BunnyPHP::getDir($config['dir'] ?? '@logs');
        $logDir = $logDir . (defined('BUNNY_APP') ? (BUNNY_APP . '/') : '') . BUNNY_CONTROLLER . '/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $this->filename = $logDir . date('Y-m-d') . '.log';
    }

    private function makeMessage($message, array $context = [], $type = ''): string
    {
        $replace = [];
        foreach ($context as $k => $v) {
            $replace['{' . $k . '}'] = $v;
        }
        return date('[Y-m-d H:i] ', time()) . '[' . $type . '] ' . strtr($message, $replace) . "\n";
    }

    public function info($message, array $context = [])
    {
        error_log($this->makeMessage($message, $context, Logger::INFO), 3, $this->filename);
    }

    public function error($message, array $context = [])
    {
        error_log($this->makeMessage($message, $context, Logger::ERROR), 3, $this->filename);
    }

    public function warn($message, array $context = [])
    {
        error_log($this->makeMessage($message, $context, Logger::WARN), 3, $this->filename);
    }

    public function debug($message, array $context = [])
    {
        if (APP_DEBUG === true) {
            error_log($this->makeMessage($message, $context, Logger::DEBUG), 3, $this->filename);
        }
    }
}