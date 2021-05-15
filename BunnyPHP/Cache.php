<?php
declare(strict_types=1);

namespace BunnyPHP;
interface Cache
{
    /**
     * Get the value related to the specified key
     * @param string $key
     * @param int $expire
     * @return mixed
     */
    public function get(string $key, int $expire = 0);

    /**
     * Verify if the specified key exists
     * @param string $key
     * @param int $expire [Optional] expire time in seconds
     * @return bool
     */
    public function has(string $key, int $expire = 0): bool;

    /**
     * Set an value by the specified key
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return mixed
     */
    public function set(string $key, $value, int $expire = 0);

    /**
     * Delete an value by the specified key
     * @param string $key
     * @return mixed
     */
    public function del(string $key);
}
