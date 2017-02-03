<?php

namespace Underscore\Contrib;

use PDO;
use RuntimeException;
use Closure;
use PDOException;
use InvalidArgumentException;
use Exception;
use Generator;

/**
 * Underscore.php
 * https://github.com/bdelespierre/underscore.php
 * (c) 2013-2017 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 *
 * Database trait is intended for composition with Underscore library. It provides a set of useful functions to
 * facilitate database interactions. It STRONGLY (if not exclusively) rely on PHP PDO's implementation so please
 * read the PDOManual (http://php.net/manual/en/class.pdo.php) before using these functions.
 */
trait Database
{
    /**
     * @see Database::connection
     */
    public static function pdo(string $name, ...$definition): ?PDO
    {
        return static::connection($name, ...$definition);
    }

    /**
     * @see Database::connection
     */
    public static function database(string $name, ...$definition): ?PDO
    {
        return static::connection($name, ...$definition);
    }

    /**
     * Get an existing database connection or initialize a new one. It accepts 5 forms:
     * + _::connection($name) >> PDO
     * + _::connection($name, $dsn, $user = "", $pass = "", $opts = []) >> NULL
     * + _::connection($name, ['dsn' => $dsn, 'username' => $user, 'password' => $pass, 'options' => $opts]) >> NULL
     * + _::connection($name, $pdo) >> NULL
     * + _::connection($name, function () { return new PDO($dsn, $user, $pass, $opts); }) >> NULL
     *
     * @category Database Functions
     *
     * @param string $name the connection name
     * @param mixed ...$definition see function's description
     *
     * @return PDO,null
     */
    public static function connection(string $name, ...$definition): ?PDO
    {
        if (empty($definition)) {
            return static::getConnection($name);
        }

        switch (true) {
            case $definition[0] instanceof PDO || is_callable($definition[0]):
                static::setConnecion($name, $definition[0]);
                return null;

            case is_array($definition[0]):
                extract(static::pick($definition, ['dsn', 'username', 'password', 'options']));
                break;

            default:
                list($dsn, $username, $password, $options) = $definition + [null, null, null, null];
                break;
        }

        static::setConnection($name, function () use ($dsn, $username, $password, $options) {
            new PDO($dsn, $username, $password, $options);
        });
    }

    /**
     * Sets multiple connection attributes at once (looping on PDO::setAttribute internaly) and return the previous
     * state of each attribute.
     *
     * @param string,PDO $connection the database connection name or PDO instance
     * @param list $attributes the attributes to set
     *
     * @return array
     */
    public static function setConnectionAttributes($connection, iterable $attributes): array
    {
        $conn = static::getConnection($connection);

        $previous = static::map(static::keys($attributes), [$conn, 'getAttribute']);
        static::each($attributes, function ($value, $attribute) use ($conn) {
            $conn->setAttribute($attribute, $value);
        });

        return $previous;
    }

    /**
     * Wraps the given function so the connection attributes will be set before the function execution and restored
     * after that. The previous attribute states are restored even if an exception occurs during function execution
     * so this function is garanteed to not tamper with the connection state. Returns the function's returned value.
     *
     * @param string,PDO $connection the database connection name or PDO instance
     * @param iterable $attributes the attributes to set
     * @param callable $function the function to wrap
     * @param object $context optional, if $query is a function then it will be bound to $context
     *
     * @return mixed
     */
    public static function usingConnectionAttributes(
        $connection,
        iterable $attributes,
        callable $function,
        $context = null
    ) {
        $conn = static::getConnection($connection);

        if ($context !== null) {
            $function = static::bind($function, $context);
        }

        return function (...$args) use ($conn, $attributes, $function) {
            try {
                $previous = static::setConnectionAttributes($conn, $attributes);
                return $function(...$args);
            } catch (Exception $e) {
                throw $e;
            } finally {
                static::setConnectionAttributes($conn, $previous);
            }
        };
    }

    /**
     * Provides a PDOStatement as a ready-to-use function. You may pass defaults as partial arguments for the query
     * execution (see PDOStatement::execute). The returned function will return the statement after execution or FALSE
     * if the execution fails or NULL if the statement cannot be prepared.
     *
     * @category Database Functions
     *
     * @param string,PDO $connection the database connection name or PDO instance
     * @param string,callable $query your query body as SQL, placeholders are allowed
     * @param iterable $defaults if provided, will pad returned function execution arguments
     * @param object $context if $query is a function then it will be bound to $context
     *
     * @return Closure
     */
    public static function statement($connection, $query, iterable &$defaults = [], $context = null): Closure
    {
        $conn = static::getConnection($connection);

        if (is_callable($query)) {
            $query = static::call($query, $context);
        }

        if (!$statement = $conn->prepare($query)) {
            throw new RuntimeException("cannot prepare statement");
        }

        return function (array $parameters = []) use ($statement, &$defaults) {
            return $statement->execute(static::defaults($parameters, $defaults)) ? $statement : false;
        };
    }

    /**
     * Executes the given statement and returns all the results at once. The default fetch-mode set on the connection
     * will be used to fetch the data (which is PDO::FETCH_BOTH unless you change it).
     *
     * @category Database Functions
     *
     * @param string,PDO $connection the database connection name or PDO instance
     * @param string,callable $query your query body as SQL, placeholders are allowed
     * @param itrerable $defaults if provided, will pad returned function execution arguments
     * @param object $context if $query is a function then it will be bound to $context
     *
     * @throws RuntimeException if the statement couldn't be executed
     *
     * @return array
     */
    public static function query($connection, $query, iterable $data = [], $context = null): array
    {
        $conn = static::getConnection($connection);

        if (false === $results = static::statement($connection, $query, $data, $context)()) {
            throw new RuntimeException("unable to execute statement");
        }

        $results->setFetchMode($conn->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        return $results->fetchAll();
    }

    /**
     * Exactly similar to query but yields the results instead of fetching everything.
     *
     * @since 0.3.0
     * @category Database Functions
     *
     * @param string,PDO $connection the database connection name or PDO instance
     * @param string,callable $query your query body as SQL, placeholders are allowed
     * @param itrerable $defaults if provided, will pad returned function execution arguments
     * @param object $context if $query is a function then it will be bound to $context
     *
     * @throws RuntimeException if the statement couldn't be executed
     *
     * @return Generator
     */
    public static function xquery($connection, $query, iterable $data = [], $context = null): Generator
    {
        $conn = static::getConnection($connection);

        if (false === $results = static::statement($connection, $query, $data, $context)()) {
            throw new RuntimeException("unable to execute statement");
        }

        $results->setFetchMode($conn->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        foreach ($results as $result) {
            yield $result;
        }
    }

    /**
     * Wraps the function inside a transaction-safe function. The transaction will be rolled-back if the wrapped
     * function returns FALSE or throws any exception, otherwise it will be committed. The wrapper function returns
     * true if the transaction was sucessfully comited, false otherwise. If the transaction cannot be started, commited
     * or rollbacked, an exception is thrown. If a PDOException is thrown during wrapped function execution, it will be
     * rethrown for debugging purposes.
     *
     * @category Database Functions
     *
     * @param string,PDO $connection the database connection name or PDO instance
     * @param callable $function the wrapped function
     * @param object $context optional, if provided will become the context of $function
     *
     * @throws RuntimeException transaction cannot be started, commited, or rollbacked
     *
     * @return Closure
     */
    public static function transaction($connection, callable $function, $context = null)
    {
        $conn = static::getConnection($connection);

        if ($context !== null) {
            $function = static::bind($function, $context);
        }

        return function (...$args) use ($conn, $function) {
            if (!$conn->beginTransaction()) {
                throw new RuntimeException("unable to begin transaction");
            }

            try {
                if (false === $result = $function(...$args)) {
                    throw new RuntimeException("error in transaction");
                }
            } catch (Exception $e) {
                throw $e;
            } finally {
                if (!$conn->rollback()) {
                    throw new RuntimeException("unable to rollback transaction");
                }
            }

            if (!$conn->commit()) {
                throw new RuntimeException("unable to commit transaction");
            }

            return $result;
        };
    }

    /**
     * Gets the PDO instance for that connection name
     *
     * @param  string,PDO $name the connection
     *
     * @throws InvalidArgumentException if name is not a valid database
     *
     * @return PDO
     */
    protected static function getConnection($name)
    {
        if ($name instanceof PDO) {
            return $name;
        }

        if (!is_string($name)) {
            throw new InvalidArgumentException("invalid database name");
        }

        if (!Container::has($key = "database.connection.{$name}")) {
            throw new InvalidArgumentException("no such database {$name}");
        }

        if (is_callable($connection = Container::get($key))) {
            $connection = Container::set($key, $connection());
        }

        if (!$connection instanceof PDO) {
            throw new InvalidArgumentException("{$name} is not a valid database");
        }

        return $connection;
    }

    /**
     * Sets the PDO instance or factory for that connection name
     *
     * @param string $name       connection's name
     * @param PDO|callable $connection the connection or factory
     *
     * @throws InvalidArgumentException if connection is invalid
     *
     * @return PDO|callable
     */
    protected static function setConnection(string $name, $connection)
    {
        if (!$conn instanceof PDO && !is_callable($connection)) {
            throw new InvalidArgumentException("invalid connection");
        }

        return Container::set("database.connection.{$name}", $connection);
    }
}
