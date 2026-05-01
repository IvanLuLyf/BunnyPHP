<?php
declare(strict_types=1);

namespace BunnyPHP\Database;

final class PgSqlDialect extends Dialect
{
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

            if ($ai !== '' && $ai === $name) {
                $sql = $name . ' serial';
            } else {
                $sql = $name . ' ' . $type;
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
}