<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 15:46
 */
class Database
{
    private static $pdo = null;
    public static function pdo()
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
        return self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $option);
    }
}