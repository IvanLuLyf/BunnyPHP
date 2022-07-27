<?php
declare(strict_types=1);

namespace BunnyPHP;

use ArrayAccess;
use ReturnTypeWillChange;

class Request implements ArrayAccess
{
    private array $param = [], $query = [];
    private bool $processed = false;

    public function __construct()
    {
        $this->query = $_GET;
        $this->process();
    }

    public function getHeader($name)
    {
        return $_SERVER['HTTP_' . strtoupper($name)] ?? null;
    }

    public static function cookie($nameOrValue, $value = '', $expire = 0, $path = '/', $domain = '', $httpOnly = true)
    {
        $time = time() - 1;
        if (is_array($nameOrValue)) {
            foreach ($nameOrValue as $k => $v) {
                setcookie($k, $v ?? '', $v === null ? $time : $expire, $path, $domain, false, $httpOnly);
            }
        } else if ($value !== '') {
            setcookie($nameOrValue, $value ?? '', $value === null ? $time : $expire, $path, $domain, false, $httpOnly);
        } else if (isset($_COOKIE[$nameOrValue])) {
            return $_COOKIE[$nameOrValue];
        }
        return null;
    }

    public static function session($nameOrValue, $value = '')
    {
        if (!session_id()) session_start();
        if (is_array($nameOrValue)) {
            foreach ($nameOrValue as $k => $v) {
                if ($v === null) unset($_SESSION[$k]);
                else$_SESSION[$k] = $v;
            }
        } else if ($value !== '') {
            if ($value === null) {
                $tmp = $_SESSION[$nameOrValue] ?? null;
                unset($_SESSION[$nameOrValue]);
                return $tmp;
            } else $_SESSION[$nameOrValue] = $value;
        } else if (isset($_SESSION[$nameOrValue])) {
            return $_SESSION[$nameOrValue];
        }
        return null;
    }

    public function getSession($name)
    {
        return self::session($name);
    }

    public function setSession($name, $value)
    {
        self::session($name, $value);
    }

    public function delSession($name)
    {
        return self::session($name, null);
    }

    public function process()
    {
        if ($this->processed || !isset($_SERVER['CONTENT_TYPE'])) return;
        $content = file_get_contents('php://input');
        $contentType = strtolower($_SERVER['CONTENT_TYPE']);
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if (strpos($contentType, 'application/json') === 0) {
            $this->param = json_decode($content, true);
        } elseif (strpos($contentType, 'application/x-www-form-urlencoded') === 0) {
            if ($method === 'post') {
                $this->param = $_POST;
                return;
            }
            parse_str($content, $this->param);
        } elseif (strpos($contentType, 'multipart/form-data;') === 0) {
            if ($method === 'post') {
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
        return isset($this->param[$offset]) || isset($this->query[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (isset($this->param[$offset])) {
            return $this->param[$offset];
        } elseif (isset($this->query[$offset])) {
            return $this->query[$offset];
        } else {
            return null;
        }
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->param[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->param[$offset]);
        unset($this->query[$offset]);
    }
}