<?php

namespace BunnyPHP;

use Generator;

interface Database
{
    /**
     * Insert data into table
     * @param array $data key-value pair
     * @param string $table table name
     * @param bool $debug
     * @return mixed
     */
    public function insert(array $data, string $table, bool $debug = false);

    /**
     * Update table data
     * @param array $data key-value pair
     * @param string $table table name
     * @param string|null $where where expression with placeholders
     * @param array $condition where param bindings
     * @param string|null $updates specify set expression
     * @param bool $debug
     * @return mixed
     */
    public function update(array $data, string $table, string $where = null, array $condition = [], string $updates = null, bool $debug = false);

    /**
     * Delete table data
     * @param string $table table name
     * @param string|null $where where expression with placeholders
     * @param array $condition where param bindings
     * @param bool $debug
     * @return mixed
     */
    public function delete(string $table, string $where = null, array $condition = [], bool $debug = false);

    /**
     * Returns a row of the result set
     * @param string $sql sql expression
     * @param array $condition param bindings
     * @param bool $debug
     * @return mixed
     */
    public function fetchOne(string $sql, array $condition = [], bool $debug = false);

    /**
     * Returns an array containing all of the result set rows
     * @param string $sql sql expression
     * @param array $condition param bindings
     * @param bool $debug
     * @return mixed
     */
    public function fetchAll(string $sql, array $condition = [], bool $debug = false);

    /**
     * Returns an iterator for traversing the result set rows
     * @param string $sql sql expression
     * @param array $condition param bindings
     * @return Generator
     */
    public function cursor(string $sql, array $condition = []): Generator;

    /**
     * Create a table
     * @param string $tableName name of table
     * @param array $columns definition of columns
     * @param array $primary primary key list
     * @param string $a_i auto increment key
     * @param array $unique unique key
     * @param bool $debug
     * @return mixed
     */
    public function createTable(string $tableName, array $columns = [], array $primary = [], string $a_i = '', array $unique = [], bool $debug = false);

    /**
     * Execute a sql
     * @param string $sql
     * @return mixed
     */
    public function exec(string $sql);

    /**
     * Query a sql and returns an array containing all of the result set rows
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql);
}
