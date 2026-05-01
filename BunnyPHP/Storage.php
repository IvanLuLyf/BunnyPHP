<?php
declare(strict_types=1);

namespace BunnyPHP;
interface Storage
{
    /**
     * Returns file content in storage
     * @param string $filename
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
     * @param string $filename Destination path/key
     * @param string $path Local source file path
     * @return string Full path/url
     */
    public function upload(string $filename, string $path): string;

    /**
     * Remove file by specific filename
     * @param string $filename
     */
    public function remove(string $filename);
}
