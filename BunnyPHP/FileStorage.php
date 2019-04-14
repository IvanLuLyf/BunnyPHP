<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/8/4
 * Time: 19:11
 */

class FileStorage implements Storage
{
    protected $uploadPath;
    protected $dir;

    public function __construct($config)
    {
        $this->dir = isset($config['dir']) ? $config['dir'] : 'upload';
        $this->uploadPath = APP_PATH . $this->dir . '/';
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0666, true);
        }
    }

    public function read($filename)
    {
        return file_get_contents($this->uploadPath . $filename);
    }

    public function write($filename, $content)
    {
        $dir = dirname($filename);
        if ($dir !== '.' && !is_dir($this->uploadPath . $dir)) {
            mkdir($this->uploadPath . $dir, 0666, true);
        }
        file_put_contents($this->uploadPath . $filename, $content);
    }

    public function upload($filename, $path)
    {
        $dir = dirname($filename);
        if ($dir !== '.' && !is_dir($this->uploadPath . $dir)) {
            mkdir($this->uploadPath . $dir, 0666, true);
        }
        move_uploaded_file($path, $this->uploadPath . $filename);
        return "/$this->dir/" . $filename;
    }

    public function remove($filename)
    {
        unlink($this->uploadPath . $filename);
    }
}