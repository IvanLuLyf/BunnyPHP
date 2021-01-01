<?php
declare(strict_types=1);

namespace BunnyPHP;
class FileCache implements Cache
{
    protected string $dir;
    protected string $cacheDir;

    public function __construct($config)
    {
        $this->dir = $config['dir'] ?? 'cache';
        $this->cacheDir = APP_PATH . $this->dir . '/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get(string $key, $expire = 0)
    {
        $filename = $this->cacheDir . md5($key);
        if (file_exists($filename)) {
            if ((filemtime($filename) + $expire > time()) || $expire === 0) {
                return file_get_contents($this->cacheDir . md5($key));
            } else {
                unlink($filename);
                return null;
            }
        } else {
            return null;
        }
    }

    public function has(string $key, $expire = 0): bool
    {
        $filename = $this->cacheDir . md5($key);
        return file_exists($filename) && ((filemtime($filename) + $expire > time()) || $expire === 0);
    }

    public function set(string $key, $value, $expire = 0)
    {
        file_put_contents($this->cacheDir . md5($key), $value);
    }

    public function del(string $key)
    {
        $filename = $this->cacheDir . md5($key);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}
