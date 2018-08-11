<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/29
 * Time: 1:03
 */

class Model implements ArrayAccess
{
    protected $table;
    protected $model;
    private $filter = '';
    private $join = '';
    private $param = array();
    protected $data = array();

    public function __construct($params = array())
    {
        if (!$this->table) {
            $this->model = get_class($this);
            $this->model = substr($this->model, 0, -5);
            $this->table = 'tp_' . strtolower($this->model);
        }
        foreach ($params as $k => $v) {
            $this->$k = $v;
            $this->data[$k] = $v;
        }
    }

    public function where($where, $param = array())
    {
        if ($where) {
            $this->filter .= ' Where ';
            if (is_array($where)) {
                $this->filter .= implode(' ', $where);
            } else {
                $this->filter .= $where;
            }
            $this->param = $param;
        }
        return $this;
    }

    public function join($tableName, $condition = array(), $mod = '')
    {
        $this->join .= " $mod Join `$tableName`";
        if ($condition) {
            $this->join .= " On (" . implode(' ', $condition) . ") ";
        }
        return $this;
    }

    public function limit($size, $start = 0)
    {
        $this->filter .= " Limit $start,$size ";
        return $this;
    }

    public function order($order = array())
    {
        if ($order) {
            $this->filter .= ' Order By ';
            $this->filter .= implode(',', $order);
        }
        return $this;
    }

    public function getAll($column = '*')
    {
        $rows = $this->fetchAll($column);
        $result = array();
        $modelName = get_class($this);
        foreach ($rows as $row) {
            $result[] = new $modelName($row);
        }
        return $result;
    }

    public function get($column = '*')
    {
        $row = $this->fetch($column);
        $modelName = get_class($this);
        $newThis = new $modelName($row);
        $newThis->data = $row;
        return $newThis;
    }

    public function fetchAll($column = '*')
    {
        $sql = sprintf("Select %s From `%s` %s %s", $column, $this->table, $this->join, $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        $this->join = '';
        $this->filter = '';
        $this->param = array();
        return $sth->fetchAll();
    }

    public function fetch($column = '*')
    {
        $sql = sprintf("Select %s From `%s` %s %s", $column, $this->table, $this->join, $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        $this->join = '';
        $this->filter = '';
        $this->param = array();
        return $sth->fetch();
    }

    public function delete()
    {
        $sql = sprintf("Delete From `%s` Where %s", $this->table, $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        $this->join = '';
        $this->filter = '';
        $this->param = array();
        return $sth->rowCount();
    }

    public function add($data)
    {
        $sql = sprintf("Insert Into `%s` %s", $this->table, $this->formatInsert($data));
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        $this->join = '';
        $this->filter = '';
        $this->param = array();
        return Database::pdo()->lastInsertId();
    }

    public function update($data)
    {
        $sql = sprintf("Update `%s` Set %s %s", $this->table, $this->formatUpdate($data), $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        $this->join = '';
        $this->filter = '';
        $this->param = array();
        return $sth->rowCount();
    }

    private function formatParam(PDOStatement $sth, $params = array())
    {
        foreach ($params as $param => &$value) {
            $param = is_int($param) ? $param + 1 : ':' . ltrim($param, ':');
            $sth->bindParam($param, $value);
        }
        return $sth;
    }

    private function formatInsert($data)
    {
        $fields = array();
        $names = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s`", $key);
            $names[] = sprintf(":%s", $key);
        }
        $field = implode(',', $fields);
        $name = implode(',', $names);
        return sprintf("(%s) values (%s)", $field, $name);
    }

    private function formatUpdate($data)
    {
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s` = :%s", $key, $key);
        }
        return implode(',', $fields);
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset) || isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->$offset || $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
        unset($this->data[$offset]);
    }
}