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
        $dir = isset($config['dir']) ? $config['dir'] : 'upload';
        $this->uploadPath = APP_PATH . $dir . '/';
    }

    public function read($filename)
    {
        return file_get_contents($this->uploadPath . $filename);
    }

    public function write($filename, $content)
    {
        file_put_contents($this->uploadPath . $filename, $content);
    }

    public function upload($filename, $path)
    {
        move_uploaded_file($path, $this->uploadPath . $filename);
    }

    public function remove($filename)
    {
        unlink($this->uploadPath . $filename);
    }

    public function geturl($filename)
    {
        return "/$this->dir/" . $filename;
    }
}