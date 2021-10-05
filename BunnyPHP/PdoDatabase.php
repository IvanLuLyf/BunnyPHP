<?php
declare(strict_types=1);

namespace BunnyPHP;

use Generator;
use PDO;
use PDOStatement;

class PdoDatabase implements Database
{
    private PDO $conn;
    private string $db_type;
    private static array $DB_TYPE = [
        'postgresql' => 'pgsql',
        'postgres' => 'pgsql',
    ];

    public function __construct($conf = [])
    {
        $urlInfo = !empty($conf['url']) ? parse_url($conf['url']) : [];
        if (!empty($conf['dsn'])) {
            $dsn = $conf['dsn'];
        } else {
            $db_type = strtolower($conf['type'] ?? self::$DB_TYPE[$urlInfo['scheme']] ?? $urlInfo['scheme'] ?? 'mysql');
            $host = $conf['host'] ?? $urlInfo['host'] ?? 'localhost';
            $port = $conf['port'] ?? $urlInfo['port'] ?? null;
            $database = $conf['database'] ?? trim($urlInfo['path'], '/');
            if ($db_type == 'mysql') {
                if (!$port) $port = 3306;
                $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            } elseif ($db_type == 'sqlite') {
                $database = $conf['database'] ?? $urlInfo['path'];
                $dsn = "sqlite:$database";
            } elseif ($db_type == 'pgsql') {
                if (!$port) $port = 5432;
                $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            }
        }
        $username = $conf['username'] ?? $urlInfo['user'] ?? '';
        $password = $conf['password'] ?? $urlInfo['pass'] ?? '';
        if (!empty($dsn)) {
            $option = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_STRINGIFY_FETCHES => false, PDO::ATTR_EMULATE_PREPARES => false];
            $this->conn = new PDO($dsn, $username, $password, $option);
            $this->db_type = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
    }

    public function insert(array $data, string $table, bool $debug = false): string
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

    public function update(array $data, string $table, string $where = null, array $condition = [], string $updates = null, bool $debug = false)
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

    public function delete(string $table, string $where = null, array $condition = [], bool $debug = false)
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

    public function fetchOne(string $sql, array $condition = [], bool $debug = false)
    {
        if ($debug) return $sql;
        $pst = $this->conn->prepare($sql);
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        return $pst->fetch();
    }

    public function fetchAll(string $sql, array $condition = [], bool $debug = false)
    {
        if ($debug) return $sql;
        $pst = $this->conn->prepare($sql);
        $pst = $this->bindParam($pst, $condition);
        $pst->execute();
        return $pst->fetchAll();
    }

    public function cursor(string $sql, array $condition = []): Generator
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

    public function exec(string $sql)
    {
        return $this->conn->exec($sql);
    }

    public function query(string $sql)
    {
        return $this->conn->query($sql);
    }

    public function createTable(string $tableName, array $columns = [], array $primary = [], string $a_i = '', array $unique = [], bool $debug = false)
    {
        $db_type = $this->db_type;
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
