<?php
declare(strict_types=1);

namespace BunnyPHP;
interface Storage
{
    /**
     * Returns file content in storage
     * @param $filename
     * @return mixed
     */
    public function read(string $filename);

    /**
     * Write content to storage in specific filename
     * @param string $filename
     * @param mixed $content
     */
    public function write(string $filename, $content);

    /**
     * Upload file
     * @param string $filename destination file path
     * @param string $path source file path
     * @return string full path
     */
    public function upload(string $filename, string $path): string;

    /**
     * Remove file by specific filename
     * @param string $filename
     */
    public function remove(string $filename);
}
