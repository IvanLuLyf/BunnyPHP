<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/29
 * Time: 1:03
 */

class Model
{
    protected $_table;
    protected $_name;
    private $_filter = '';
    private $_join = '';
    private $_param = [];
    private $_column = [];
    private $_has_where = false;
    private $_debug = false;

    public function __construct($name = '')
    {
        if (!$this->_table) {
            if (!empty($name)) {
                $this->_name = $name;
            } else {
                $this->_name = substr(get_class($this), 0, -5);
            }
            $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($this->_name)));
            $this->_table = DB_PREFIX . strtolower($dashed);
        }
    }

    public static function create($debug = false)
    {
        $table = self::name();
        $vars = get_class_vars(get_called_class());
        $pk = isset($vars['_pk']) ? $vars['_pk'] : [];
        $ai = isset($vars['_ai']) ? $vars['_ai'] : '';
        $uk = isset($vars['_uk']) ? $vars['_uk'] : [];
        return Database::getInstance()->createTable($table, $vars['_column'], $pk, $ai, $uk, $debug);
    }

    public static function name()
    {
        $vars = get_class_vars(get_called_class());
        if (isset($vars['_table'])) {
            return $vars['_table'];
        } else {
            $name = substr(get_called_class(), 0, -5);
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
    }

    public function debug()
    {
        $this->_debug = true;
        return $this;
    }

    public function where($where, $param = [])
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

    public function join($tableName, $condition = [], $select = [], $mod = 'left')
    {
        if (substr($tableName, -5) == "Model") {
            $tableName = $tableName::name();
        }
        if (count($select) == 0) {
            $this->_column[] = $tableName . ".*";
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
        $this->_join .= " on (" . implode(' and ', $conditionArr) . ")";
        return $this;
    }

    public function limit($size, $start = 0)
    {
        if (DB_TYPE === 'pgsql') {
            $this->_filter .= " limit $size offset $start";
        } else {
            $this->_filter .= " limit $start,$size";
        }
        return $this;
    }

    public function order($order = [])
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
        $result = Database::getInstance()->fetchOne($this->buildSelect($columns), $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function fetchAll($columns = '*')
    {
        $result = Database::getInstance()->fetchAll($this->buildSelect($columns), $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function cursor($columns = '*')
    {
        $result = Database::getInstance()->fetchAll($this->buildSelect($columns), $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function delete()
    {
        $result = Database::getInstance()->delete($this->_table, $this->_filter, $this->_param, $this->_debug);
        $this->reset();
        return $result;
    }

    public function add($data = [])
    {
        return Database::getInstance()->insert($data, $this->_table, $this->_debug);
    }

    public function update($data = [], $what = null)
    {
        $result = Database::getInstance()->update($data, $this->_table, $this->_filter, $this->_param, $what, $this->_debug);
        $this->reset();
        return $result;
    }

    private function buildSelect($columns = '*')
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