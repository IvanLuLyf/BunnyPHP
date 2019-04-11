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

    public function __construct($name = "")
    {
        if (!$this->_table) {
            if (isset($name) && $name != "") {
                $this->_name = $name;
            } else {
                $this->_name = substr(get_class($this), 0, -5);
            }
            $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($this->_name)));
            $this->_table = DB_PREFIX . strtolower($dashed);
        }
    }

    public static function create()
    {
        $name = substr(get_called_class(), 0, -5);
        $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($name)));
        $table = DB_PREFIX . strtolower($dashed);
        $vars = get_class_vars(get_called_class());
        $pk = isset($vars['_pk']) ? $vars['_pk'] : [];
        $ai = isset($vars['_ai']) ? $vars['_ai'] : '';
        return Database::getInstance()->createTable($table, $vars['_column'], $pk, $ai);
    }

    public static function name()
    {
        $name = substr(get_called_class(), 0, -5);
        $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($name)));
        return DB_PREFIX . strtolower($dashed);
    }

    private function reset()
    {
        $this->_filter = '';
        $this->_join = '';
        $this->_param = [];
        $this->_column = [];
        $this->_has_where = false;
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
            $tableName = substr($tableName, 0, -5);
            $dashed = strtolower(preg_replace('/([A-Z])/', '_$1', lcfirst($tableName)));
            $tableName = DB_PREFIX . strtolower($dashed);
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
        $this->_join .= " on (" . implode(' and ', $conditionArr) . ") ";
        return $this;
    }

    public function limit($size, $start = 0)
    {
        if (constant("DB_TYPE") == 'pgsql') {
            $this->_filter .= " limit $size offset $start";
        } else {
            $this->_filter .= " limit $start,$size ";
        }
        return $this;
    }

    public function order($order = [])
    {
        if (is_array($order)) {
            $this->_filter .= ' order by ';
            $this->_filter .= implode(',', $order);
        } else {
            $this->_filter .= " order by $order ";
        }
        return $this;
    }

    public function fetch($columns = '*')
    {
        $selection = $columns;
        if ($this->_join != '') {
            if ($columns == '*') {
                $selection = $this->_table . '.*';
                $selection .= ',' . implode(',', $this->_column);
            } elseif (is_array($columns)) {
                foreach ($columns as &$column) {
                    $column = $this->_table . $column;
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
        $sql = "select {$selection} from {$this->_table} {$this->_join} {$this->_filter}";
        $result = Database::getInstance()->fetchOne($sql, $this->_param);
        $this->reset();
        return $result;
    }

    public function fetchAll($columns = '*')
    {
        $selection = $columns;
        if ($this->_join != '') {
            if ($columns == '*') {
                $selection = $this->_table . '.*';
                $selection .= ',' . implode(',', $this->_column);
            } elseif (is_array($columns)) {
                foreach ($columns as &$column) {
                    $column = $this->_table . $column;
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
        $sql = "select {$selection} from {$this->_table} {$this->_join} {$this->_filter}";
        $result = Database::getInstance()->fetchAll($sql, $this->_param);
        $this->reset();
        return $result;
    }

    public function delete()
    {
        $result = Database::getInstance()->delete($this->_table, $this->_filter, $this->_param);
        $this->reset();
        return $result;
    }

    public function add($data = [])
    {
        return Database::getInstance()->insert($data, $this->_table);
    }

    public function update($data = [], $what = null)
    {
        $result = Database::getInstance()->update($data, $this->_table, $this->_filter, $this->_param, $what);
        $this->reset();
        return $result;
    }
}