<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/1/1
 * Time: 15:08
 */
class Database
{
    private $conn;
    private $debug = false;
    private static $instance;

    private function __construct($debug = false)
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
            $option = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $option);
        }
        $this->debug = $debug;
    }

    public static function getInstance($debug = false): Database
    {
        if (self::$instance == null) {
            self::$instance = new Database($debug);
        }
        return self::$instance;
    }

    public function insert(array $data, $table)
    {
        $keys = implode(',', array_keys($data));
        $values = implode(',:', array_keys($data));
        $sql = "insert into {$table} ({$keys}) values(:{$values})";
        if ($this->debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($data as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        $pst->execute();
        return $this->conn->lastInsertId();
    }

    public function update(array $data, $table, $where = null, $condition = [], $updates = null)
    {
        if ($updates === null) {
            $sets = [];
            foreach ($data as $key => $value) {
                $sets[] = "{$key} = :{$key}";
            }
            $updates = implode(',', $sets);
        }
        $where = $where == null ? '' : ' WHERE ' . $where;
        $sql = "update {$table} set {$updates} {$where}";
        if ($this->debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($data as $k => &$v) {
            $pst->bindParam(':' . $k, $v);
        }
        foreach ($condition as $k => &$v) {
            if (is_int($k)) {
                $pst->bindParam($k + 1, $v);
            } else {
                $pst->bindParam(':' . $k, $v);
            }
        }
        $pst->execute();
        return $pst->rowCount();
    }

    public function delete($table, $where = null, $condition = [])
    {
        $where = $where == null ? '' : ' WHERE ' . $where;
        $sql = "delete from {$table} {$where}";
        if ($this->debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($condition as $k => &$v) {
            if (is_int($k)) {
                $pst->bindParam($k + 1, $v);
            } else {
                $pst->bindParam(':' . $k, $v);
            }
        }
        $pst->execute();
        return $pst->rowCount();
    }

    public function fetchOne($sql, $condition = [])
    {
        if ($this->debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($condition as $k => &$v) {
            if (is_int($k)) {
                $pst->bindParam($k + 1, $v);
            } else {
                $pst->bindParam(':' . $k, $v);
            }
        }
        $pst->execute();
        return $pst->fetch();
    }

    public function fetchAll($sql, $condition = [])
    {
        if ($this->debug) {
            return $sql;
        }
        $pst = $this->conn->prepare($sql);
        foreach ($condition as $k => &$v) {
            if (is_int($k)) {
                $pst->bindParam($k + 1, $v);
            } else {
                $pst->bindParam(':' . $k, $v);
            }
        }
        $pst->execute();
        return $pst->fetchAll();
    }

    public function createTable($tableName, $columns = [], $primary = [], $a_i = '', $unique = [])
    {
        $db_type = strtolower(DB_TYPE);
        if ($db_type == 'mysql') {
            $columnsData = [];
            foreach ($columns as $name => $info) {
                $columnData = $name . ' ';
                if (is_array($info)) {
                    $columnData .= implode(' ', $info);
                } else {
                    $columnData .= ' ' . $info;
                }
                if ($a_i == $name) {
                    $columnData .= ' auto_increment ';
                }
                $columnsData[] = $columnData;
            }
            $c = implode(',', $columnsData);
            $pk = '';
            if ($primary) {
                $pk .= ',primary key(' . implode(',', $primary) . ')';
            }
            $uk = '';
            if ($unique) {
                $uk .= ',unique key(' . implode(',', $unique) . ')';
            }
            $sql = "create table {$tableName}({$c}{$pk}{$uk});";
            if ($this->debug) {
                return $sql;
            }
            return $this->conn->exec($sql);
        } elseif ($db_type == 'pgsql') {
            $columnsData = [];
            foreach ($columns as $name => $info) {
                $columnData = $name . ' ';
                if ($a_i == $name) {
                    $columnData .= ' serial ';
                } else {
                    if (is_array($info)) {
                        $columnData .= implode(' ', $info);
                    } else {
                        $columnData .= ' ' . $info;
                    }
                }
                if (in_array($name, $primary)) {
                    $columnData .= ' primary key ';
                }
                $columnsData[] = $columnData;
            }
            $c = implode(',', $columnsData);
            $uk = '';
            if ($unique) {
                $uk .= ',unique(' . implode(',', $unique) . ')';
            }
            $sql = "create table {$tableName}({$c}{$uk});";
            if ($this->debug) {
                return $sql;
            }
            return $this->conn->exec($sql);
        } elseif ($db_type == 'sqlite') {
            $columnsData = [];
            foreach ($columns as $name => $info) {
                $columnData = $name . ' ';
                if (is_array($info)) {
                    $columnData .= implode(' ', $info);
                } else {
                    $columnData .= ' ' . $info;
                }
                if (in_array($name, $primary)) {
                    $columnData .= ' primary key ';
                }
                if ($a_i == $name) {
                    $columnData .= ' autoincrement ';
                }
                $columnsData[] = $columnData;
            }
            $c = implode(',', $columnsData);
            $uk = '';
            if ($unique) {
                $uk .= ',unique(' . implode(',', $unique) . ')';
            }
            $sql = "create table {$tableName}({$c}{$uk});";
            if ($this->debug) {
                return $sql;
            }
            return $this->conn->exec($sql);
        } else {
            return -1;
        }
    }
}