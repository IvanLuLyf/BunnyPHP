<?php
declare(strict_types=1);

namespace BunnyPHP\Database;

final class TableSchema
{
    public static function fromLegacy(
        string $table,
        array  $columns = [],
        array  $primary = [],
        string $autoIncrement = '',
        array  $unique = []
    ): array
    {
        $col = [];
        foreach ($columns as $name => $info) {
            if (is_array($info)) {
                $type = (string)($info[0] ?? '');
                $constraints = array_values(array_filter(array_slice($info, 1), fn($x) => $x !== null && $x !== ''));
            } else {
                $type = (string)$info;
                $constraints = [];
            }
            $col[(string)$name] = [
                'type' => trim($type),
                'constraints' => $constraints,
            ];
        }

        $schema = [
            'table' => $table,
            'columns' => $col,
        ];
        if (!empty($primary)) $schema['primary'] = array_values($primary);
        if ($autoIncrement !== '') $schema['auto_increment'] = $autoIncrement;
        if (!empty($unique)) $schema['unique'] = $unique;
        return $schema;
    }
}