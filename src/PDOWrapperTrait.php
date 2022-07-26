<?php

/**
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Designcise\PDO;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

use function array_merge;
use function array_values;
use function gettype;
use function is_numeric;
use function is_string;
use function is_scalar;
use function is_bool;

trait PDOWrapperTrait
{
    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::connect()
     */
    abstract public function connect();

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::disconnect()
     */
    abstract public function disconnect();

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::beginTransaction()
     */
    public function beginTransaction(): void
    {
        $this->connect();
        $this->getPdo()->beginTransaction();
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::transact()
     */
    public function transact(string $query, array $args = []): int
    {
        $args = $this->boolArgsToInt($args);

        $this->connect();

        $sth = $this->getPdo()->prepare($query);
        $bindVals = $this->queryBindValues($sth, $query, $args);

        $sth->execute($bindVals ? null : $args);

        return $sth->rowCount();
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::commit()
     */
    public function commit(): bool
    {
        $this->connect();
        return $this->getPdo()->commit();
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::rollback()
     */
    public function rollback(): bool
    {
        $this->connect();
        return $this->getPdo()->rollBack();
    }


    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::fetch()
     */
    public function fetch(
        string $query,
        array $args = [],
        int $style = PDO::FETCH_ASSOC
    ) {
        return $this->dbFetch($query, $args, '', $style);
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::fetchAll()
     */
    public function fetchAll(
        string $query,
        array $args = [],
        int $style = PDO::FETCH_ASSOC
    ): array {
        return $this->dbFetch($query, $args, 'All', $style);
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::fetchColumn()
     */
    public function fetchCol(string $query, array $args = [])
    {
        return $this->dbFetch(
            $query,
            $args,
            '',
            PDO::FETCH_COLUMN
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     *
     * @see PdoInterface::fetchObject()
     */
    public function fetchObject(
        string $query,
        array $values = [],
        string $class = 'stdClass',
        array $args = [],
        bool $argsOverwrite = false
    ) {
        if (empty($class)) {
            throw new RuntimeException('Class name cannot be empty');
        }

        // run query:
        // using fetch style as `PDO::FETCH_ASSOC` so supplied `$args` can
        // be merged with result set
        if (empty($result = $this->fetch($query, $values, PDO::FETCH_ASSOC))) {
            return false;
        }

        // columns (in order of the result set) + additional `$args` supplied
        $newInstanceArgs = ($argsOverwrite)
            ? $args
            : array_values(array_merge($result, $args));

        // copy values from db to class
        // @internal splat operator does not work with associative indexed arrays
        return new $class(...array_values($newInstanceArgs));
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     *
     * @see PdoInterface::fetchObjects()
     */
    public function fetchObjects(
        string $query,
        array $values = [],
        string $class = 'stdClass',
        array $args = [],
        bool $argsOverwrite = false
    ): array {
        if (empty($class)) {
            throw new RuntimeException('Class name cannot be empty');
        }

        // run query:
        // using fetch style as `PDO::FETCH_ASSOC` so supplied `$args` can
        // be merged with result set
        if (empty($result = $this->fetchAll($query, $values, PDO::FETCH_ASSOC))) {
            return [];
        }

        $objs = [];

        foreach ($result as $row) {
            if (empty($row)) {
                continue;
            }

            // columns (in order of the result set) + additional `$args` supplied
            $newInstanceArgs = $argsOverwrite
                ? $args
                : array_values(array_merge($row, $args));

            // copy values from db to class
            // @internal splat operator does not work with associative indexed arrays
            $objs[] = new $class(...$newInstanceArgs);
        }

        return $objs;
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::fetchGroup()
     */
    public function fetchGroup(
        string $query,
        array $args = [],
        int $style = PDO::FETCH_COLUMN
    ): array {
        return $this->dbFetch(
            $query,
            $args,
            'All',
            $style | PDO::FETCH_GROUP
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::run()
     */
    public function run(string $query, array $args = []): PDOStatement
    {
        $this->connect();

        $args = $this->boolArgsToInt($args);

        $sth = $this->getPdo()->prepare($query);
        $bindVals = $this->queryBindValues($sth, $query, $args);

        $sth->execute($bindVals ? null : $args);

        return $sth;
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::query()
     */
    public function query(string $statement)
    {
        $this->connect();
        return $this->getPdo()->query($statement);
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::errorCode()
     */
    public function errorCode()
    {
        $this->connect();
        return $this->getPdo()->errorCode();
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::errorInfo()
     */
    public function errorInfo(): array
    {
        $this->connect();
        return $this->getPdo()->errorInfo();
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::setAttribute()
     */
    public function setAttribute(int $prop, $val): bool
    {
        return $this->getPdo()->setAttribute($prop, $val);
    }

    /**
     * {@inheritdoc}
     *
     * @see PdoInterface::getPdo()
     */
    abstract public function getPdo(): PDO;

    /**
     * Common method to fetch rows from the database.
     *
     * @param string $query
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     * @param string $fetchMethodSuffix 'All', 'Column', or ''.
     *
     * @param int $fetchStyle
     * @return mixed Row(s) returned by the query, or an empty array (or boolean false)
     *               if no record found.
     */
    private function dbFetch(
        string $query,
        array $args = [],
        string $fetchMethodSuffix = '',
        int $fetchStyle = PDO::FETCH_BOTH
    ): mixed {
        $sth = $this->run($query, $args);

        $fetchMethod = "fetch{$fetchMethodSuffix}";

        if (($result = $sth->{$fetchMethod}($fetchStyle)) === false) {
            // return empty string for columns, empty array for all others
            return ($fetchMethodSuffix === '' && $fetchStyle === PDO::FETCH_COLUMN)
                ? false
                : [];
        }

        return $result;
    }


    /**
     * Bind values to corresponding data types for PDO execution.
     * (Must be called after PDO's prepare() and before execute()).
     *
     * @param PDOStatement $sth
     * @param string $query
     * @param array $args An array of arguments for query placeholders (such as :val, ?).
     *
     * @return bool
     *
     * @throws PDOException
     */
    private function queryBindValues(
        PDOStatement $sth,
        string $query,
        array $args = []
    ): bool {
        // any bound variables (in the format ':placeholder' or '?')?
        if (
            empty($args) || (
                ! preg_match('/:[^\s]*/m', $query)
                && ! preg_match('/[(=<>,][\s]*\?[\s]*/m', $query)
            )
        ) {
            return false;
        }

        foreach ($args as $key => $value) {
            $param = false;

            if (empty($value)) {
                $value = null;
                $param = PDO::PARAM_NULL;
            } elseif (is_numeric($value)) {
                $value = (int) $value;
                $param = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $param = PDO::PARAM_BOOL;
            } elseif (is_string($value)) {
                $param = PDO::PARAM_STR;
            } elseif (! is_scalar($value)) {
                $type = gettype($value);
                throw new PDOException(
                    "Cannot bind value of type '{$type}' to placeholder '{$key}'"
                );
            }

            if ($param) {
                $sth->bindValue(
                    (((is_numeric($key)) ? '' : ':') . $key),
                    $value,
                    $param,
                );
            }
        }

        return true;
    }

    private function boolArgsToInt(array $args): array
    {
        // fix php bug: SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type boolean: ""
        // workaround: convert booleans to int
        array_walk_recursive($args, function (&$value) {
            if (is_bool($value)) {
                $value = (int)$value;
            }
        });

        return $args;
    }
}
