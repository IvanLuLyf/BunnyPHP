<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/1/1
 * Time: 15:08
 */

namespace BunnyPHP;

interface Database
{
    public function insert(array $data, $table, $debug = false);

    public function update(array $data, $table, $where = null, $condition = [], $updates = null, $debug = false);

    public function delete($table, $where = null, $condition = [], $debug = false);

    public function fetchOne($sql, $condition = [], $debug = false);

    public function fetchAll($sql, $condition = [], $debug = false);

    public function cursor($sql, $condition = []);

    public function createTable($tableName, $columns = [], $primary = [], $a_i = '', $unique = [], $debug = false);

    public function exec($sql);

    public function query($sql);
}