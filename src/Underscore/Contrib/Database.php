<?php

namespace Underscore\Contrib;

/**
 * Underscore.php
 * https: *github.com/bdelespierre/underscore.php
 * (c) 2013-2014 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 *
 * Database trait is intended for composition with Underscore library. It provides a set of useful functions to
 * facilitate database interactions. It STRONGLY (if not exclusively) rely on PHP PDO's implementation so please
 * read the PDOManual (http://php.net/manual/en/class.pdo.php) before using these functions.
 */
trait Database
{
	protected static $pool = [];

	/**
	 * @see Database::connection
	 */
	public static function pdo($name, $definition = null)
	{
		return static::connection($name, array_slice(func_get_args(), 1));
	}

	/**
	 * @see Database::connection
	 */
	public static function database($name, $definition = null)
	{
		return static::connection($name, array_slice(func_get_args(), 1));
	}

	/**
	 * Get an existing database connection or initialize a new one. Database connections are managed as PDO instances.
	 * If such instance doesn't exists or could not be initialized, will return NULL.
	 *
	 * @category Database Functions
	 *
	 * @param string $name the connection name
	 * @param array,scalar $definition multiple, the equivalent of PDO::__construct arguments
	 *
	 * @return PDO
	 */
	public static function connection($name, $definition = null)
	{
		if (empty($definition))
			return static::get(static::$pool, $name);

		if (static::isScalar($definition))
			list(, $dsn, $username, $password, $options) = func_get_args() + [null, '', '', '', []];
		else
			extract(static::pick($definition, ['dsn', 'username', 'password', 'options']));

		return static::$pool[$name] = new \PDO($dsn, $username, $password, $options);
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
	public static function setConnectionAttributes($connection, $attributes)
	{
		static::_connection($connection);

		$previous = static::map(static::keys($attributes), [$connection, 'getAttribute']);
		static::each($attributes, function($value, $attribute) use ($connection) {
			$connection->setAttribute($attribute, $value);
		});

		return $previous;
	}

	/**
	 * Wraps the given function so the connection attributes will be set before the function execution and restored
	 * after that. The previous attribute states are restored even if an exception occurs during function execution
	 * so this function is garanteed to not tamper with the connection state. Returns the function's returned value.
	 *
	 * @param string,PDO $connection the database connection name or PDO instance
	 * @param list $attributes the attributes to set
	 * @param callable $function the function to wrap
	 * @param object $context optional, if $query is a function then it will be bound to $context
	 *
	 * @return mixed
	 */
	public static function usingConnectionAttributes($connection, $attributes, callable $function, $context = null)
	{
		static::_connection($connection);
		static::_associate($function, $context);

		return function() use ($connection, $function) {
			$previous = static::setConnectionAttributes($connection, $attributes);
			return static::lastly(function() use ($function) {
				return call_user_func_array($function, func_get_args());
			}, function() use ($previous) {
				static::setConnectionAttributes($connection, $previous);
			});
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
	 * @param list $defaults optional, if provided, will pad returned function execution arguments
	 * @param object $context optional, if $query is a function then it will be bound to $context
	 *
	 * @return closure
	 */
	public static function statement($connection, $query, & $defaults = [], $context = null)
	{
		static::_connection($connection);

		if (static::isFunction($query)) {
			static::_associate($query, $context);
			$query = $query();
		}

		if (!$statement = $connection->prepare($query))
			return null;

		return function($data = []) use ($statement, $query, & $defaults) {
			if (static::isScalar($data))
				$data = func_get_args();

			if (!$statement->execute(static::defaults($data, $defaults)))
				return false;

			return $statement;
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
	 * @param list $defaults optional, if provided, will pad returned function execution arguments
	 * @param object $context optional, if $query is a function then it will be bound to $context
	 *
	 * @return array
	 */
	public static function query($connection, $query, $data = [], $context = null)
	{
		if (!$statement = static::statement($connection, $query, $data, $context))
			throw new \RuntimeException("unable to prepare statement");

		$results = $statement();

		if ($results === false)
			throw new \RuntimeException("unable to execute statement");

		$results->setFetchMode($connection->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE));
		return $results->fetchAll();
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
	 * @return closure
	 */
	public static function transaction($connection, callable $function, $context = null)
	{
		static::_connection($connection);
		static::_associate($function, $context);

		return function() use ($connection, $function) {
			if (!$connection->beginTransaction())
				throw new \RuntimeException("unable to begin transaction");

			try {
				if (call_user_func_array($function, func_get_args()) === false)
					goto rollback;
			} catch (\PDOException $e) {
				throw $e;
			} catch (\Exception $e) {
				goto rollback;
			}

			if (!$connection->commit())
				throw new \RuntimeException("unable to commit transaction");

			return true;

			rollback:
			if (!$connection->rollback())
				throw new \RuntimeException("unable to rollback transaction");

			return false;
		};
	}

	/**
	 * Transform the connection into a PDO instance. Throw if the connection is invalid or isn't registered.
	 *
	 * @category Internal Functions
	 *
	 * @param string,PDO $connection the database connection name or PDO instance
	 *
	 * @return void
	 */
	protected static function _connection(& $connection)
	{
		if (static::isString($connection))
			$connection = static::connection($connection);

		if (!$connection instanceof \PDO)
			throw new \InvalidArgumentException("invalid connection");
	}
}