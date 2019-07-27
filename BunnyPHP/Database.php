<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/1/1
 * Time: 15:08
 */

namespace BunnyPHP;

use PDO;
use PDOStatement;

class Database
{
    private $conn;
    private static $instance;

    private function __construct()
    {
        $db_type = strtolower(DB_TYPE);
        if ($db_type == 'mysql') {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        } elseif ($db_type == 'sqlite') {
            $dsn = "sqlite:" . DB_NAME;
        } elseif ($db_type == 'pgsql') {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . "";
        }
        if (!empty($dsn)) {
            $option = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_STRINGIFY_FETCHES => false, PDO::ATTR_EMULATE_PREPARES => false];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $option);
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function insert(array $data, $table, $debug = false)
    {
        $keys = implode(',', array_keys($data));
        $values = implode(',:', array_keys($data));
        $sql = "insert into {$table} ({$keys}) values(:{$values})";
        if ($debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($data as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $this->conn->lastInsertId();
    }

    public function update(array $data, $table, $where = null, $condition = [], $updates = null, $debug = false)
    {
        if ($updates === null) {
            $sets = [];
            foreach ($data as $key => $value) {
                $sets[] = "{$key} = :{$key}";
            }
            $updates = implode(',', $sets);
        }
        $where = $where == null ? '' : ' where ' . $where;
        $sql = "update {$table} set {$updates} {$where}";
        if ($debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($data as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        return $pst->rowCount();
    }

    public function delete($table, $where = null, $condition = [], $debug = false)
    {
        $where = $where == null ? '' : ' WHERE ' . $where;
        $sql = "delete from {$table} {$where}";
        if ($debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        return $pst->rowCount();
    }

    public function fetchOne($sql, $condition = [], $debug = false)
    {
        if ($debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        return $pst->fetch();
    }

    public function fetchAll($sql, $condition = [], $debug = false)
    {
        if ($debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        return $pst->fetchAll();
    }

    public function cursor($sql, $condition = [])
    {
        $pst = $this->conn->prepare($sql);
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        while ($row = $pst->fetch()) {
            yield $row;
        }
    }

    private function bindParam(PDOStatement $statement, array $data = []): PDOStatement
    {
        foreach ($data as $k => &$v) {
            if (is_int($k)) {
                $statement->bindParam($k + 1, $v);
            } else {
                $statement->bindParam(':' . $k, $v);
            }
        }
        return $statement;
    }

    public function exec($sql)
    {
        return $this->conn->exec($sql);
    }

    public function createTable($tableName, $columns = [], $primary = [], $a_i = '', $unique = [], $debug = false)
    {
        $db_type = strtolower(DB_TYPE);
        $columnsData = [];
        foreach ($columns as $name => $info) {
            $columnData = $name . ' ';
            if ($a_i == $name && $db_type === 'pgsql') {
                $columnData .= ' serial ';
            } else {
                if (is_array($info)) {
                    $columnData .= implode(' ', $info);
                } else {
                    $columnData .= ' ' . $info;
                }
            }
            if ($a_i == $name) {
                if ($db_type === 'mysql') {
                    $columnData .= ' auto_increment ';
                } elseif ($db_type === 'sqlite') {
                    $columnData = $name . ' integer primary key autoincrement ';
                }
            }
            $columnsData[] = $columnData;
        }
        $c = implode(',', $columnsData);
        $pk = '';
        if ($primary) {
            $pk .= ',primary key(' . implode(',', $primary) . ')';
        }
        if ($db_type === 'sqlite' && !empty($a_i)) {
            $pk = '';
        }
        $uk = '';
        if ($unique) {
            foreach ($unique as $item) {
                if (is_array($item)) {
                    $uk .= ',unique key(' . implode(',', $item) . ')';
                } else {
                    $uk .= ',unique key(' . $item . ')';
                }
            }
        }
        $sql = "create table {$tableName}({$c}{$pk}{$uk});";
        if (!empty($sql)) {
            if ($debug) {
                return $sql;
            }
            return $this->conn->exec($sql);
        } else {
            return -1;
        }
    }
}