<?php

namespace BunnyPHP;

interface Cache
{
    /**
     * Get the value related to the specified key
     * @param string $key
     * @param int $expire
     * @return mixed
     */
    public function get(string $key, $expire = 0);

    /**
     * Verify if the specified key exists
     * @param string $key
     * @param int $expire [Optional] expire time in seconds
     * @return bool
     */
    public function has(string $key, $expire = 0): bool;

    /**
     * Set an value by the specified key
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return mixed
     */
    public function set(string $key, $value, $expire = 0);

    /**
     * Delete an value by the specified key
     * @param string $key
     * @return mixed
     */
    public function del(string $key);
}
