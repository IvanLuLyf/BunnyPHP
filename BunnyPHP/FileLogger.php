<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/4/19
 * Time: 22:31
 */

class FileLogger implements Logger
{
    protected $filename;

    public function __construct($config)
    {
        $this->filename = isset($config['filename']) ? $config['filename'] : APP_PATH . 'log.log';
    }

    private function makeMessage($message, array $context = [], $type = '')
    {
        $replace = [];
        foreach ($context as $k => $v) {
            $replace['{' . $k . '}'] = $v;
        }
        return date("[Y-m-d H:i] ", time()) . '[' . $type . '] ' . strtr($message, $context) . "\n";
    }

    public function info($message, array $context = [])
    {
        error_log($this->makeMessage($message, $context, 'INFO'), 3, $this->filename);
    }

    public function error($message, array $context = [])
    {
        error_log($this->makeMessage($message, $context, 'ERROR'), 3, $this->filename);
    }

    public function warn($message, array $context = [])
    {
        error_log($this->makeMessage($message, $context, "WARN"), 3, $this->filename);
    }

    public function debug($message, array $context = [])
    {
        if (APP_DEBUG === true) {
            error_log($this->makeMessage($message, $context, "DEBUG"), 3, $this->filename);
        }
    }
}