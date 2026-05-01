<?php
declare(strict_types=1);

namespace BunnyPHP\Database;

final class SqlServerDialect extends Dialect
{
    public function quoteId(string $name): string
    {
        $name = trim($name);
        if ($name === '' || $name === '*') return $name;
        if ($name[0] === '[' && substr($name, -1) === ']') return $name;
        return '[' . str_replace(']', ']]', $name) . ']';
    }

    public function compileCreateTable(array $schema): string
    {
        $table = $schema['table'] ?? '';
        $columns = $schema['columns'] ?? [];
        $primary = $schema['primary'] ?? [];
        $ai = $schema['auto_increment'] ?? '';
        $unique = $schema['unique'] ?? [];

        $columnSql = [];
        foreach ($columns as $name => $def) {
            $type = trim((string)($def['type'] ?? ''));
            $constraints = $def['constraints'] ?? [];
            if (!is_array($constraints)) $constraints = [];
            $sql = $name . ' ' . $type;
            if ($ai !== '' && $ai === $name) {
                $sql .= ' identity(1,1)';
            }
            if ($constraints) $sql .= ' ' . implode(' ', $constraints);
            $columnSql[] = trim($sql);
        }

        $tail = [];
        if (!empty($primary)) {
            $tail[] = 'primary key(' . implode(',', $primary) . ')';
        }
        if (!empty($unique)) {
            foreach ($unique as $item) {
                $cols = is_array($item) ? $item : [$item];
                $tail[] = 'unique(' . implode(',', $cols) . ')';
            }
        }

        $all = array_merge($columnSql, $tail);
        return 'create table ' . $table . '(' . implode(',', $all) . ');';
    }

    public function limitOffset(int $limit, int $offset = 0): string
    {
        $limit = max(0, $limit);
        $offset = max(0, $offset);
        return ' offset ' . $offset . ' rows fetch next ' . $limit . ' rows only';
    }
}