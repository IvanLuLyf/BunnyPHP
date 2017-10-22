<?php

/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2017/10/5
 * Time: 16:00
 */
class Model extends Sql
{
    protected $model;

    public function __construct()
    {
        if (!$this->table) {
            $this->model = get_class($this);
            $this->model = substr($this->model, 0, -5);
            $this->table = 'tp_' . strtolower($this->model);
        }
    }
}