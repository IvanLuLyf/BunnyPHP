<?php
declare(strict_types=1);

namespace BunnyPHP;

use Generator;
use BunnyPHP\Database\Dialect;
use BunnyPHP\Database\TableSchema;
use PDO;
use PDOStatement;
use RuntimeException;

class PdoDatabase implements Database
{
    private PDO $conn;
    private string $db_type;
    private Dialect $dialect;
    private static array $DB_TYPE = [
        'postgresql' => 'pgsql',
        'postgres' => 'pgsql',
        'mssql' => 'sqlsrv',
        'sqlserver' => 'sqlsrv',
        'oracle' => 'oci',
    ];

    public function __construct($conf = [])
    {
        [$dsn, $username, $password] = $this->buildDsn((array)$conf);
        if ($dsn === '') {
            throw new RuntimeException('Database DSN is empty');
        }
        $this->conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->db_type = (string)$this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->dialect = Dialect::forDriver($this->db_type);
    }

    private function buildDsn(array $conf): array
    {
        $urlInfo = !empty($conf['url']) ? parse_url((string)$conf['url']) : [];
        $username = (string)($urlInfo['user'] ?? ($conf['username'] ?? ''));
        $password = (string)($urlInfo['pass'] ?? ($conf['password'] ?? ''));

        if (!empty($conf['dsn'])) {
            return [(string)$conf['dsn'], $username, $password];
        }

        $scheme = !empty($urlInfo['scheme'])
            ? strtolower((string)$urlInfo['scheme'])
            : strtolower((string)($conf['type'] ?? 'mysql'));
        $dbType = strtolower(self::$DB_TYPE[$scheme] ?? $scheme);

        $host = (string)($urlInfo['host'] ?? ($conf['host'] ?? 'localhost'));
        $port = $urlInfo['port'] ?? ($conf['port'] ?? null);
        $database = isset($urlInfo['path']) ? trim((string)$urlInfo['path'], '/') : (string)($conf['database'] ?? '');

        if ($dbType === 'mysql') {
            if (!$port) $port = 3306;
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        } elseif ($dbType === 'sqlite') {
            $db = $conf['database'] ?? ($urlInfo['path'] ?? '');
            $dsn = 'sqlite:' . $db;
        } elseif ($dbType === 'pgsql') {
            if (!$port) $port = 5432;
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
        } elseif ($dbType === 'sqlsrv') {
            if (!$port) $port = 1433;
            $server = $host . ',' . $port;
            $dsn = "sqlsrv:Server=$server;Database=$database";
        } elseif ($dbType === 'oci') {
            if (!$port) $port = 1521;
            if ($host !== '') {
                $dsn = "oci:dbname=//$host:$port/$database";
            } else {
                $dsn = "oci:dbname=$database";
            }
        } else {
            $dsn = '';
        }

        return [$dsn, $username, $password];
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
        $pst->execute($data);
        return $this->conn->lastInsertId();
    }

    public function update(array $data, string $table, string $where = '', array $condition = [], string $updates = '', bool $debug = false)
    {
        if ($updates === '') {
            $sets = [];
            foreach ($data as $key => $value) {
                $sets[] = "{$key} = :{$key}";
            }
            $updates = implode(',', $sets);
        }
        $where = $where === '' ? '' : ' where ' . $where;
        $sql = "update {$table} set {$updates} {$where}";
        if ($debug) return ['sql' => $sql, 'bind' => $condition];
        $pst = $this->conn->prepare($sql);
        $this->bindNamed($pst, $data);
        $this->bindAny($pst, $condition);
        $pst->execute();
        return $pst->rowCount();
    }

    public function delete(string $table, string $where = '', array $condition = [], bool $debug = false)
    {
        $where = $where === '' ? '' : ' WHERE ' . $where;
        $sql = "delete from {$table} {$where}";
        if ($debug) return ['sql' => $sql, 'bind' => $condition];
        $pst = $this->conn->prepare($sql);
        $this->bindAny($pst, $condition);
        $pst->execute();
        return $pst->rowCount();
    }

    public function fetchOne(string $sql, array $condition = [], bool $debug = false)
    {
        if ($debug) return ['sql' => $sql, 'bind' => $condition];
        $pst = $this->conn->prepare($sql);
        $this->bindAny($pst, $condition);
        $pst->execute();
        return $pst->fetch();
    }

    public function fetchAll(string $sql, array $condition = [], bool $debug = false)
    {
        if ($debug) return ['sql' => $sql, 'bind' => $condition];
        $pst = $this->conn->prepare($sql);
        $this->bindAny($pst, $condition);
        $pst->execute();
        return $pst->fetchAll();
    }

    public function cursor(string $sql, array $condition = []): Generator
    {
        $pst = $this->conn->prepare($sql);
        $this->bindAny($pst, $condition);
        $pst->execute();
        while ($row = $pst->fetch()) {
            yield $row;
        }
    }

    private function bindNamed(PDOStatement $statement, array $data = []): void
    {
        foreach ($data as $k => $v) {
            if (!is_string($k) || $k === '') continue;
            $name = ($k[0] === ':') ? $k : ':' . $k;
            $statement->bindValue($name, $v);
        }
    }

    private function bindAny(PDOStatement $statement, array $data = []): void
    {
        foreach ($data as $k => $v) {
            if (is_int($k)) {
                $statement->bindValue($k + 1, $v);
                continue;
            }
            $name = $k;
            $name = ($name !== '' && $name[0] === ':') ? $name : ':' . $name;
            $statement->bindValue($name, $v);
        }
    }

    public function compileSelect(array $query): string
    {
        return $this->dialect->compileSelect($query);
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
        $schema = TableSchema::fromLegacy($tableName, $columns, $primary, $a_i, $unique);
        $sql = $this->dialect->compileCreateTable($schema);
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
