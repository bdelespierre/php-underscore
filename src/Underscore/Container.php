<?php

namespace Underscore;

/**
 * Underscore.php
 * https: *github.com/bdelespierre/underscore.php
 * (c) 2013-2017 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 *
 * Underscore's data container. Used to garantee that every forged instance of Underscore share the same data.
 * DO NOT TOY AROUND WITH THIS CLASS unless you know what you're doing.
 */
class Container
{
    /**
     * @var array
     */
    protected static $data = [];

    /**
     * @param  string  $key
     *
     * @return boolean
     */
    public static function has($key): bool
    {
        return isset(static::$data[$key]) || array_key_exists($key, static::$data);
    }

    /**
     * @param  string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public static function &get($key, $default = null)
    {
        return static::$data[$key] ?? $default;
    }

    /**
     * @return array
     */
    public static function all()
    {
        return static::$data;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public static function set($key, $value)
    {
        return static::$data[$key] = $value;
    }
}
