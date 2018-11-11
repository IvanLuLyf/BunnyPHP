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

    private function reset()
    {
        $this->_filter = '';
        $this->_join = '';
        $this->_param = [];
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

    public function join($tableName, $condition = [], $mod = '')
    {
        $this->_join .= " $mod join $tableName";
        if (is_array($condition)) {
            $this->_join .= " on (" . implode(' ', $condition) . ") ";
        } else {
            $this->_join .= " on ($condition) ";
        }
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

    public function fetch($column = '*')
    {
        if (is_array($column)) {
            $column = implode(',', $column);
        }
        if ($this->_has_where) {
            $this->_filter = ' where ' . $this->_filter;
        }
        $sql = "select {$column} from {$this->_table} {$this->_join} {$this->_filter}";
        $result = Database::getInstance()->fetchOne($sql, $this->_param);
        $this->reset();
        return $result;
    }

    public function fetchAll($column = '*')
    {
        if (is_array($column)) {
            $column = implode(',', $column);
        }
        if ($this->_has_where) {
            $this->_filter = ' where ' . $this->_filter;
        }
        $sql = "select {$column} from {$this->_table} {$this->_join} {$this->_filter}";
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
        Database::getInstance()->insert($data, $this->_table);
    }

    public function update($data = [])
    {
        $result = Database::getInstance()->update($data, $this->_table, $this->_filter, $this->_param);
        $this->reset();
        return $result;
    }
}