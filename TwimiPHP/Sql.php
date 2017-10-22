<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 15:50
 */
class Sql
{
    protected $table;
    protected $primary = 'id';
    private $filter = '';
    private $param = array();

    public function where($where = array(), $param = array())
    {
        if ($where) {
            $this->filter .= ' WHERE ';
            $this->filter .= implode(' ', $where);
            $this->param = $param;
        }
        return $this;
    }

    public function order($order = array())
    {
        if ($order) {
            $this->filter .= ' ORDER BY ';
            $this->filter .= implode(',', $order);
        }
        return $this;
    }

    public function fetchAll()
    {
        $sql = sprintf("select * from `%s` %s", $this->table, $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        return $sth->fetchAll();
    }

    public function fetch()
    {
        $sql = sprintf("select * from `%s` %s", $this->table, $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        return $sth->fetch();
    }

    public function delete($id)
    {
        $sql = sprintf("delete from `%s` where `%s` = :%s", $this->table, $this->primary, $this->primary);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, [$this->primary => $id]);
        $sth->execute();
        return $sth->rowCount();
    }

    public function add($data)
    {
        $sql = sprintf("insert into `%s` %s", $this->table, $this->formatInsert($data));
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        return $sth->rowCount();
    }

    // 修改数据
    public function update($data)
    {
        $sql = sprintf("update `%s` set %s %s", $this->table, $this->formatUpdate($data), $this->filter);
        $sth = Database::pdo()->prepare($sql);
        $sth = $this->formatParam($sth, $data);
        $sth = $this->formatParam($sth, $this->param);
        $sth->execute();
        return $sth->rowCount();
    }

    public function formatParam(PDOStatement $sth, $params = array())
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
}