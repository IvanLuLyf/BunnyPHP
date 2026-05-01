<?php
declare(strict_types=1);

namespace BunnyPHP\Database;

final class MySqlDialect extends Dialect
{
    public function quoteId(string $name): string
    {
        $name = trim($name);
        if ($name === '' || $name === '*') return $name;
        if ($name[0] === '`' && substr($name, -1) === '`') return $name;
        return '`' . str_replace('`', '``', $name) . '`';
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
            if ($constraints) $sql .= ' ' . implode(' ', $constraints);
            if ($ai !== '' && $ai === $name) $sql .= ' auto_increment';
            $columnSql[] = trim($sql);
        }

        $tail = [];
        if (!empty($primary)) {
            $tail[] = 'primary key(' . implode(',', $primary) . ')';
        }
        if (!empty($unique)) {
            foreach ($unique as $item) {
                $cols = is_array($item) ? $item : [$item];
                $tail[] = 'unique key(' . implode(',', $cols) . ')';
            }
        }

        $all = array_merge($columnSql, $tail);
        return 'create table ' . $table . '(' . implode(',', $all) . ');';
    }
}