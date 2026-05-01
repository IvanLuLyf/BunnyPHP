<?php
declare(strict_types=1);

namespace BunnyPHP\Database;

abstract class Dialect
{
    public static function forDriver(string $driver): Dialect
    {
        $driver = strtolower(trim($driver));
        switch ($driver) {
            case 'mysql':
                return new MySqlDialect();
            case 'pgsql':
                return new PgSqlDialect();
            case 'sqlite':
                return new SqliteDialect();
            case 'sqlsrv':
            case 'dblib':
                return new SqlServerDialect();
            case 'oci':
            case 'oci8':
                return new OracleDialect();
            default:
                return new SqliteDialect();
        }
    }

    abstract public function compileCreateTable(array $schema): string;

    public function compileSelect(array $query): string
    {
        $select = (string)($query['select'] ?? '*');
        $from = (string)($query['from'] ?? '');
        $join = (string)($query['join'] ?? '');
        $where = (string)($query['where'] ?? '');
        $order = (string)($query['order'] ?? '');
        $limit = $query['limit'] ?? null;
        $offset = (int)($query['offset'] ?? 0);

        $sql = 'select ' . $select . ' from ' . $from . $join;
        if ($where !== '') $sql .= ' where ' . $where;
        if ($order !== '') $sql .= ' order by ' . $order;
        if ($limit !== null) $sql .= $this->limitOffset((int)$limit, $offset);
        return $sql;
    }

    public function quoteId(string $name): string
    {
        $name = trim($name);
        if ($name === '' || $name === '*') return $name;
        if ($name[0] === '"' && substr($name, -1) === '"') return $name;
        return '"' . str_replace('"', '""', $name) . '"';
    }

    public function quotePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '*') return $path;
        if (strpos($path, '(') !== false || strpos($path, ' ') !== false) return $path;
        $parts = explode('.', $path);
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '' || $p === '*') $out[] = $p;
            else $out[] = $this->quoteId($p);
        }
        return implode('.', $out);
    }

    public function limitOffset(int $limit, int $offset = 0): string
    {
        $limit = max(0, $limit);
        $offset = max(0, $offset);
        return ' limit ' . $limit . ' offset ' . $offset;
    }
}