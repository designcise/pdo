<?php

/**
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 */

namespace Designcise\PDO;

use PDO;
use PDOStatement;
use stdClass;

interface PDOWrapperInterface
{
    /**
     * Connect to the database.
     */
    public function connect();
    
    /**
     * Disconnect from the database.
     */
    public function disconnect();
    
    /**
     * Must Start a new transaction.
     *
     * @see: PDO::beginTransaction()
     */
    public function beginTransaction();
    
    /**
     * Operations conducted in a transaction (such as insert, update, delete).
     *
     * @param string $query The SQL query to execute.
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     *
     * @return int Number of rows affected in the transaction.
     */
    public function transact(string $query, array $args = []): int;
    
    /**
     * Must commit + end a transaction.
     *
     * @see: PDO::commit()
     */
    public function commit();
    
    /**
     * Must roll back a transaction.
     *
     * @see: PDO::rollback()
     */
    public function rollback();
    
    /**
     * Must fetch the next row from a result set.
     *
     * @param string $query The SQL query to execute.
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     * @param int $style Fetch style.
     *
     * @return array|bool Database row returned by the query, or empty array if no record
     *                    found. Returns false for no record found if `$style = PDO::FETCH_COLUMN`
     *
     * @see: PDO::fetch()
     */
    public function fetch(
        string $query,
        array $args = [],
        int $style = PDO::FETCH_ASSOC
    );
    
    /**
     * Must return an array containing all of the result set rows.
     *
     * @param string $query The SQL query to execute.
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     * @param int $style Fetch style.
     *
     * @return array Database rows returned by the query, or empty array if no record found.
     *
     * @see: PDO::fetchAll()
     */
    public function fetchAll(
        string $query,
        array $args = [],
        int $style = PDO::FETCH_ASSOC
    ): array;
    
    /**
     * Must return a single column from the next row of a result set.
     *
     * @param string $query The SQL query to execute.
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     *
     * @return mixed Single column return by the query, or boolean false if no record found.
     *
     * @see: PDO::fetchColumn()
     */
    public function fetchCol(string $query, array $args = []);
    
    /**
     * Fetches one row from the database as an object where the column
     * values are mapped to object properties.
     *
     * @param string $query The SQL query to execute.
     * @param array $values Values to bind to the query.
     * @param string $class
     * @param array $args Additional arguments to pass to each object constructor.
     * @param bool $argsOverwrite If false, $args are appended to db column data.
     *
     * @return mixed $class object instance, or boolean false if no record found.
     */
    public function fetchObject(
        string $query,
        array $values = [],
        string $class = stdClass::class,
        array $args = [],
        bool $argsOverwrite = false
    );
    
    /**
     * Fetches a sequential array of rows from the database; the rows
     * are returned as objects where the column values are mapped to
     * object properties.
     *
     * @param string $query The SQL query to execute.
     * @param array $values Values to bind to the query.
     * @param string $class
     * @param array $args Additional arguments to pass to each object constructor.
     * @param bool $argsOverwrite If false, $args are appended to db column data.
     *
     * @return array
     */
    public function fetchObjects(
        string $query,
        array $values = [],
        string $class = stdClass::class,
        array $args = [],
        bool $argsOverwrite = false
    ): array;
    
    /**
     * Returns data grouped by the values of the specified column in the result set.
     * The first column will be the index key.
     *
     * @param string $query The SQL query to execute.
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     * @param int $style Fetch style.
     *
     * @return array Single column return by the query, or empty array if no record found.
     *
     * @see: PDO::fetchColumn()
     */
    public function fetchGroup(
        string $query,
        array $args = [],
        int $style = PDO::FETCH_COLUMN
    ): array;
    
    /**
     * Execute the query.
     *
     * @param string $query The SQL query to execute.
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     *
     * @return PDOStatement
     */
    public function run(string $query, array $args = []): PDOStatement;
    
    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * @param string $statement The SQL statement to execute.
     *
     * @return PDOStatement|bool Returns false on failure.
     *
     * @see: PDO::query()
     */
    public function query(string $statement);
    
    /**
     * Gets the most recent error code.
     *
     * @return mixed
     *
     * @see: PDO::errorCode()
     */
    public function errorCode();
    
    /**
     * Gets the most recent error info.
     *
     * @return array
     *
     * @see: PDO::setAttribute()
     */
    public function errorInfo(): array;
    
    /**
     * Sets an attribute on the database handle.
     *
     * @param int $prop
     * @param mixed $val
     *
     * @return bool
     *
     * @see: PDO::setAttribute()
     */
    public function setAttribute(int $prop, $val): bool;
    
    /**
     * Get the PDO instance.
     *
     * @return PDO
     */
    public function getPdo(): PDO;
}
