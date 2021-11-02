<?php
declare(strict_types=1);

namespace BunnyPHP;
class FileStorage implements Storage
{
    protected string $uploadPath;
    protected string $dir;

    public function __construct($config)
    {
        $upload = $config['dir'] ?? '@upload';
        if ($upload[0] === '@') {
            $this->dir = '/' . str_replace('@', '', $upload);
        } else {
            $this->dir = $config['url'];
        }
        $this->uploadPath = BunnyPHP::getDir($upload);
    }

    public function read(string $filename)
    {
        return file_get_contents($this->uploadPath . $filename);
    }

    public function write(string $filename, $content)
    {
        $dir = dirname($filename);
        if ($dir !== '.' && !is_dir($this->uploadPath . $dir)) {
            mkdir($this->uploadPath . $dir, 0777, true);
        }
        file_put_contents($this->uploadPath . $filename, $content);
    }

    public function upload(string $filename, string $path): string
    {
        $dir = dirname($filename);
        if ($dir !== '.' && !is_dir($this->uploadPath . $dir)) {
            mkdir($this->uploadPath . $dir, 0777, true);
        }
        move_uploaded_file($path, $this->uploadPath . $filename);
        return "{$this->dir}/$filename";
    }

    public function remove(string $filename)
    {
        unlink($this->uploadPath . $filename);
    }
}
