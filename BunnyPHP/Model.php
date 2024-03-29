<?php
declare(strict_types=1);

namespace BunnyPHP;
class Model
{
    protected string $_table;
    protected string $_name;
    private string $_filter = '';
    private string $_join = '';
    private array $_param = [];
    private array $_column = [];
    private bool $_has_where = false;
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
        $this->_filter = '';
        $this->_join = '';
        $this->_param = [];
        $this->_column = [];
        $this->_has_where = false;
        $this->_debug = false;
    }

    public function debug(): self
    {
        $this->_debug = true;
        return $this;
    }

    public function where($where, $param = []): self
    {
        if ($where) {
            $this->_has_where = true;
            if (is_array($where)) {
                $this->_filter .= implode(' ', $where);
            } else {
                $this->_filter .= $where;
            }
            $this->_param = $param;
        }
        return $this;
    }

    public function join($tableName, $condition = [], $select = [], $mod = 'left'): self
    {
        if (substr($tableName, -5) == 'Model') {
            $tableName = $tableName::name();
        }
        if (count($select) == 0) {
            $this->_column[] = $tableName . '.*';
        } else {
            foreach ($select as $item) {
                if (is_array($item)) {
                    $this->_column[] = sprintf($item[1], "{$tableName}.{$item[0]}");
                } else {
                    $this->_column[] = "{$tableName}.{$item}";
                }
            }
        }
        $this->_join .= " $mod join $tableName";
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
        $this->_join .= ' on (' . implode(' and ', $conditionArr) . ')';
        return $this;
    }

    public function limit($size, $start = 0): self
    {
        $this->_filter .= " limit $size offset $start";
        return $this;
    }

    public function order($order = []): self
    {
        if (is_array($order)) {
            $this->_filter .= ' order by ';
            $this->_filter .= implode(',', $order);
        } else {
            $this->_filter .= " order by $order";
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
        $result = BunnyPHP::getDatabase()->delete($this->_table, $this->_filter, $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function add($data = [])
    {
        return BunnyPHP::getDatabase()->insert($data, $this->_table, $this->_debug);
    }

    public function update($data = [], $what = null)
    {
        $result = BunnyPHP::getDatabase()->update($data, $this->_table, $this->_filter, $this->_param, $what, $this->_debug);
        $this->reset();
        return $result;
    }

    private function buildSelect($columns = '*'): string
    {
        $selection = $columns;
        if (!empty($this->_join)) {
            if ($columns == '*') {
                $selection = $this->_table . '.* ,' . implode(',', $this->_column);
            } elseif (is_array($columns)) {
                foreach ($columns as &$column) {
                    $column = $this->_table . '.' . $column;
                }
                $selection = implode(',', array_merge($columns, $this->_column));
            } else {
                $selection = '*';
            }
        } else {
            if (is_array($columns)) {
                $selection = implode(',', $columns);
            }
        }
        if ($this->_has_where) {
            $this->_filter = ' where ' . $this->_filter;
        }
        return "select {$selection} from {$this->_table}{$this->_join}{$this->_filter}";
    }
}