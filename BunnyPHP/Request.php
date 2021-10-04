<?php
declare(strict_types=1);

namespace BunnyPHP;

use ArrayAccess;

class Request implements ArrayAccess
{
    private array $param = [];
    private bool $processed = false;

    public function __construct()
    {
        $this->process();
    }

    public function getHeader($name)
    {
        $header_name = 'HTTP_' . strtoupper($name);
        if (isset($_SERVER[$header_name])) {
            return $_SERVER[$header_name];
        }
        return null;
    }

    public function getSession($name)
    {
        if (!session_id()) session_start();
        if (empty($_SESSION[$name]) === false) {
            return $_SESSION[$name];
        } else {
            return null;
        }
    }

    public function setSession($name, $value)
    {
        if (!session_id()) session_start();
        $_SESSION[$name] = $value;
    }

    public function delSession($name)
    {
        if (!session_id()) session_start();
        $ret = null;
        if (empty($_SESSION[$name]) === false) {
            $ret = $_SESSION[$name];
        }
        unset($_SESSION[$name]);
        return $ret;
    }

    public function process()
    {
        if ($this->processed || !isset($_SERVER['CONTENT_TYPE'])) return;
        $content = file_get_contents('php://input');
        $contentType = strtolower($_SERVER['CONTENT_TYPE']);
        if (strpos($contentType, 'application/json') === 0) {
            $this->param = json_decode($content, true);
        } elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0) {
            parse_str($content, $this->param);
        } elseif (strpos($contentType, 'multipart/form-data;') === 0) {
            if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
                $this->param = $_POST;
                return;
            }
            $boundary = substr($content, 0, strpos($content, "\r\n"));
            if (empty($boundary)) return;
            $parts = array_slice(explode($boundary, $content), 1);
            foreach ($parts as $part) {
                if ($part == "--\r\n") break;
                $part = ltrim($part, "\r\n");
                list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);
                $raw_headers = explode("\r\n", $raw_headers);
                $headers = [];
                foreach ($raw_headers as $header) {
                    list($name, $value) = explode(':', $header);
                    $headers[strtolower($name)] = ltrim($value, ' ');
                }
                if (isset($headers['content-disposition'])) {
                    preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
                    $name = $matches[2];
                    if (isset($matches[4])) {
                        if (isset($_FILES[$name])) continue;
                        $filename = $matches[4];
                        $filename_parts = pathinfo($filename);
                        $tmp_name = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);
                        $_FILES[$name] = ['error' => 0, 'name' => $filename, 'tmp_name' => $tmp_name, 'size' => strlen($body), 'type' => $headers['content-type']];
                        file_put_contents($tmp_name, $body);
                    } else {
                        $this->param[$name] = substr($body, 0, strlen($body) - 2);
                    }
                }
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->param[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->param[$offset])) {
            return $this->param[$offset];
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->param[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->param[$offset]);
    }
}