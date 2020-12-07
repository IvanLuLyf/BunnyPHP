<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/4/7
 * Time: 2:05
 */

namespace BunnyPHP;

class Request implements \ArrayAccess
{
    private $param = [];

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
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $content = file_get_contents('php://input');
            if (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') === 0) {
                $this->param = json_decode($content, true);
            } elseif (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/x-www-form-urlencoded') === 0) {
                parse_str($content, $this->param);
            } elseif (strpos(strtolower($_SERVER['CONTENT_TYPE']), 'multipart/form-data;') === 0) {
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
                        $filename = null;
                        $tmp_name = null;
                        preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches);
                        list(, $type, $name) = $matches;
                        if (isset($matches[4])) {
                            if (isset($_FILES[$matches[2]])) {
                                continue;
                            }
                            $filename = $matches[4];
                            $filename_parts = pathinfo($filename);
                            $tmp_name = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);
                            $_FILES[$matches[2]] = ['error' => 0, 'name' => $filename, 'tmp_name' => $tmp_name, 'size' => strlen($body), 'type' => $value];
                            file_put_contents($tmp_name, $body);
                        } else {
                            $this->param[$name] = substr($body, 0, strlen($body) - 2);
                        }
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
    }

    public function offsetUnset($offset)
    {
        unset($this->param[$offset]);
    }
}