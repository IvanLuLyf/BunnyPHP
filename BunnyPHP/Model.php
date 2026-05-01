<?php
declare(strict_types=1);

namespace BunnyPHP;
class Model
{
    protected string $_table;
    protected string $_name;
    private string $_where = '';
    private array $_select = [
        'order' => '',
        'limit' => null,
        'offset' => 0,
        'join' => '',
        'join_columns' => [],
    ];
    private array $_param = [];
    private bool $_debug = false;

    public function __construct($name = '')
    {
        if (empty($this->_table)) {
            if (!empty($name)) {
                $this->_name = $name;
            } else {
                $pos = strrpos(get_called_class(), '\\');
                $this->_name = substr(get_called_class(), $pos !== false ? $pos + 1 : 0, -5);
            }
            $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($this->_name)));
            $this->_table = DB_PREFIX . strtolower($dashed);
        }
    }

    public static function create($debug = false)
    {
        $table = self::name();
        $vars = get_class_vars(get_called_class());
        $pk = $vars['_pk'] ?? [];
        $ai = $vars['_ai'] ?? '';
        $uk = $vars['_uk'] ?? [];
        return BunnyPHP::getDatabase()->createTable($table, $vars['_column'], $pk, $ai, $uk, $debug);
    }

    public static function name(): string
    {
        $vars = get_class_vars(get_called_class());
        if (isset($vars['_table'])) {
            return $vars['_table'];
        } else {
            $pos = strrpos(get_called_class(), '\\');
            $name = substr(get_called_class(), $pos !== false ? $pos + 1 : 0, -5);
            $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($name)));
            return DB_PREFIX . strtolower($dashed);
        }
    }

    private function reset()
    {
        $this->_where = '';
        $this->_select = [
            'order' => '',
            'limit' => null,
            'offset' => 0,
            'join' => '',
            'join_columns' => [],
        ];
        $this->_param = [];
        $this->_debug = false;
    }

    public function debug(): self
    {
        $this->_debug = true;
        return $this;
    }

    public function where($where, $param = []): self
    {
        if (!$where) return $this;

        $piece = trim(is_array($where) ? implode(' ', $where) : (string)$where);
        if ($piece !== '') $this->_where = $this->_where ? ($this->_where . ' and (' . $piece . ')') : $piece;
        if ($param) $this->_param = array_merge($this->_param, (array)$param);
        return $this;
    }

    public function join($tableName, $condition = [], $select = [], $mod = 'left'): self
    {
        if (substr($tableName, -5) == 'Model') {
            $tableName = $tableName::name();
        }
        if (count($select) == 0) {
            $this->_select['join_columns'][] = $tableName . '.*';
        } else {
            foreach ($select as $item) {
                if (is_array($item)) {
                    $this->_select['join_columns'][] = sprintf($item[1], "{$tableName}.{$item[0]}");
                } else {
                    $this->_select['join_columns'][] = "{$tableName}.{$item}";
                }
            }
        }
        $this->_select['join'] .= " $mod join $tableName";
        $conditionArr = [];
        foreach ($condition as $k => $v) {
            if (is_array($v)) {
                $conditionArr[] = "{$tableName}.{$v[0]}={$this->_table}.{$v[1]}";
            } else if (is_int($k)) {
                $conditionArr[] = "{$tableName}.{$v}={$this->_table}.{$v}";
            } else {
                $conditionArr[] = "{$tableName}.{$k}={$v}";
            }
        }
        $this->_select['join'] .= ' on (' . implode(' and ', $conditionArr) . ')';
        return $this;
    }

    public function limit($size, $start = 0): self
    {
        $this->_select['limit'] = (int)$size;
        $this->_select['offset'] = (int)$start;
        return $this;
    }

    public function order($order = []): self
    {
        if (is_array($order)) {
            $this->_select['order'] = implode(',', $order);
        } else {
            $this->_select['order'] = (string)$order;
        }
        return $this;
    }

    public function fetch($columns = '*')
    {
        $result = BunnyPHP::getDatabase()->fetchOne($this->buildSelect($columns), $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function fetchAll($columns = '*')
    {
        $result = BunnyPHP::getDatabase()->fetchAll($this->buildSelect($columns), $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function cursor($columns = '*')
    {
        if ($this->_debug) {
            $result = BunnyPHP::getDatabase()->fetchAll($this->buildSelect($columns), $this->_param, true);
        } else {
            $result = BunnyPHP::getDatabase()->cursor($this->buildSelect($columns), $this->_param);
        }
        $this->reset();
        return $result;
    }

    public function delete()
    {
        $where = $this->_where !== '' ? $this->_where : null;
        $result = BunnyPHP::getDatabase()->delete($this->_table, $where, $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function add($data = [])
    {
        return BunnyPHP::getDatabase()->insert($data, $this->_table, $this->_debug);
    }

    public function update($data = [], $what = null)
    {
        $where = $this->_where !== '' ? $this->_where : null;
        $result = BunnyPHP::getDatabase()->update($data, $this->_table, $where, $this->_param, $what, $this->_debug);
        $this->reset();
        return $result;
    }

    private function buildSelect($columns = '*'): string
    {
        $selection = $columns;
        if (!empty($this->_select['join'])) {
            if ($columns == '*') {
                $selection = $this->_table . '.* ,' . implode(',', $this->_select['join_columns']);
            } elseif (is_array($columns)) {
                foreach ($columns as &$column) {
                    $column = $this->_table . '.' . $column;
                }
                $selection = implode(',', array_merge($columns, $this->_select['join_columns']));
            } else {
                $selection = '*';
            }
        } else {
            if (is_array($columns)) {
                $selection = implode(',', $columns);
            }
        }
        $db = BunnyPHP::getDatabase();
        $query = [
            'select' => $selection,
            'from' => $this->_table,
            'join' => $this->_select['join'],
            'where' => $this->_where,
            'order' => $this->_select['order'],
            'limit' => $this->_select['limit'],
            'offset' => $this->_select['offset'],
        ];
        return $db->compileSelect($query);
    }
}