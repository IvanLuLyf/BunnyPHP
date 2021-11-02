<?php
declare(strict_types=1);

namespace BunnyPHP;
class FileCache implements Cache
{
    protected string $cacheDir;

    public function __construct($config)
    {
        $this->cacheDir = BunnyPHP::getDir($config['dir'] ?? '@cache');
    }

    private function k(string $key): string
    {
        return $this->cacheDir . sha1($key);
    }

    public function get(string $key, int $expire = 0)
    {
        $filename = $this->k($key);
        if (file_exists($filename)) {
            if ((filemtime($filename) + $expire > time()) || $expire === 0) {
                return file_get_contents($filename);
            } else {
                unlink($filename);
                return null;
            }
        } else {
            return null;
        }
    }

    public function has(string $key, int $expire = 0): bool
    {
        $filename = $this->k($key);
        return file_exists($filename) && ((filemtime($filename) + $expire > time()) || $expire === 0);
    }

    public function set(string $key, $value, int $expire = 0)
    {
        file_put_contents($this->k($key), $value);
    }

    public function del(string $key)
    {
        $filename = $this->k($key);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}
