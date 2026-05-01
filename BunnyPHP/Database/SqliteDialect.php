<?php
declare(strict_types=1);

namespace BunnyPHP\Database;

final class SqliteDialect extends Dialect
{
    public function compileCreateTable(array $schema): string
    {
        $table = $schema['table'] ?? '';
        $columns = $schema['columns'] ?? [];
        $primary = $schema['primary'] ?? [];
        $ai = $schema['auto_increment'] ?? '';
        $unique = $schema['unique'] ?? [];

        $columnSql = [];
        $hasAi = ($ai !== '');

        foreach ($columns as $name => $def) {
            $type = trim((string)($def['type'] ?? ''));
            $constraints = $def['constraints'] ?? [];
            if (!is_array($constraints)) $constraints = [];

            if ($hasAi && $ai === $name) {
                // SQLite requires INTEGER PRIMARY KEY AUTOINCREMENT
                $columnSql[] = trim($name . ' integer primary key autoincrement');
                continue;
            }
            $sql = $name . ' ' . $type;
            if ($constraints) $sql .= ' ' . implode(' ', $constraints);
            $columnSql[] = trim($sql);
        }

        $tail = [];
        // if autoincrement exists, primary key is already declared on that column
        if (!$hasAi && !empty($primary)) {
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

}