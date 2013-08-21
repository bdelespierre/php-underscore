<?php
/**
 * This file is part of Underscore.php
 *
 * Copyright (c) 2013 Benjamin Delespierre
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Underscore is a utility-belt library for PHP that provides a lot of the functional programming support that
 * you would expect in Prototype.php (or Ruby), but without extending any of the built-in PHP objects.
 *
 * Underscore provides 80-odd functions that support both the usual functional suspects: map, select,
 * invoke — as well as more specialized helpers: function binding, php templating, deep equality testing,
 * and so on.
 *
 * @package Underscore
 * @author Benjamin Delespierre
 */
class Underscore {

    // Collection Functions (Arrays or Objects)
    // ----------------------------------------

    /**
     * Loop breaker (escape character)
     *
     * @var string
     */
    const BREAKER = "\x1b";

    /**
     * Iterates over a list of elements, yielding each in turn to an iterator function. The iterator is bound to
     * the context object, if one is passed. Each invocation of iterator is called with three arguments:
     * (element, index, list). If list is an object, iterator's arguments will be (value, key, list).
     *
     * @note You can stop the loop by returning Underscore::BREAKER in the $iterator function.
     *
     * @param collection  $list     The item list
     * @param callable    $iterator The iteration function
     * @param object     [$context] The object context for the iterator function
     *
     * @throws InvalidArgumentException If $list is not a valid collection
     *
     * @return void
     */
    public static function each ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        if (empty($list))
            return;

        if (!static::_isSequence($list))
            throw new InvalidArgumentException("cannot iterate over " . static::_getType($list));

        foreach ($list as $index => & $item)
            if ($iterator($item, $index, $list) === static::BREAKER)
                return;
    }

    /**
     * Alias of Underscore::map
     */
    public static function collect ($list, callable $iterator, $context = null) {
        return static::map($list, $iterator, $context);
    }

    /**
     * Produces a new array of values by mapping each value in list through a transformation function (iterator). The
     * iterator is bound to the context object, if one is passed. Each invocation of iterator is called with three
     * arguments: (element, index, list). If list is an object, iterator's arguments will be (value, key, list).
     *
     * @param collection  $list      The list of items to map
     * @param callable    $iterator  The iterator function
     * @param object     [$context]  The object context for the iterator function
     *
     * @return array
     */
    public static function map ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $result = [];
        static::each($list, function ($item, $index, $list) use ($iterator, & $result) {
            $result[$index] = $iterator($item, $index, $list);
        });

        return $result;
    }

    /**
     * Alias of Underscore::reduce
     */
    public static function inject ($list, callable $iterator, $memo, $context = null) {
        return static::reduce($list, $iterator, $memo, $context);
    }

    /**
     * Alias of Underscore::reduce
     */
    public static function foldl ($list, callable $iterator, $memo, $context = null) {
        return static::reduce($list, $iterator, $memo, $context);
    }

    /**
     * Also known as inject and foldl, reduce boils down a list of values into a single value. Memo is the initial
     * state of the reduction, and each successive step of it should be returned by iterator. The iterator is passed
     * four arguments: the memo, then the value and index (or key) of the iteration, and finally a reference to
     * the entire list.
     *
     * @param collection  $list     The item list to reduce
     * @param callable    $iterator The reduction function
     * @param mixed       $memo     The initial state of reduction
     * @param object     [$context] The object context for reduction function
     *
     * @return mixed
     */
    public static function reduce ($list, callable $iterator, $memo, $context = null) {
        static::_associate($iterator, $context);

        static::each($list, function ($item, $index, $list) use ($iterator, & $memo) {
            $memo = $iterator($memo, $item, $index, $list);
        });

        return $memo;
    }

    /**
     * Alias of Underscore::reduceRight
     */
    public static function foldr ($list, callable $iterator, $memo, $context = null) {
        return static::reduceRight($list, $iterator, $memo, $context);
    }

    /**
     * The right-associative version of reduce
     *
     * @param collection  $list     The item list to reduce
     * @param callable    $iterator The reduction function
     * @param mixed       $memo     The initial state of reduction
     * @param object     [$context] The object context for reduction function
     *
     * @return mixed
     */
    public static function reduceRight ($list, callable $iterator, $memo, $context = null) {
        return static::reduce(array_reverse(static::toArray($list)), $iterator, $memo, $context);
    }

    /**
     * Looks through each value in the list, returning the first one that passes a truth test (iterator), or null if
     * no value passes the test. The function returns as soon as it finds an acceptable element, and doesn't traverse
     * the entire list.
     *
     * @param collection  $list     The item list
     * @param callable    $iterator The iteration function
     * @param object     [$context] The object context for iteration function
     *
     * @return mixed
     */
    public static function find ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $result = null;
        static::each($list, function ($item) use ($iterator, & $result) {
            if ($iterator($item)) {
                $result = $item;
                return static::breaker;
            }
        });

        return $result;
    }

    /**
     * Alias of Underscore::filter
     */
    public static function select ($list, callable $iterator, $context = null) {
        return static::filter($list, $iterator, $context);
    }

    /**
     * Looks through each value in the list, returning an array of all the values that pass a truth test (iterator).
     *
     * @param collection  $list     The item list
     * @param callable    $iterator The filtering function
     * @param object     [$context] The object context for filtering function
     *
     * @return array
     */
    public static function filter ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $result = [];
        static::each($list, function ($item, $index) use ($iterator, & $result) {
            if ($iterator($item))
                $result[$index] = $item;
        });

        return $result;
    }

    /**
     * Looks through each value in the list, returning an array of all the values that contain all of the key-value
     * pairs listed in properties.
     *
     * @param collection $list       The items list
     * @param collection $properties The filter properties
     *
     * @return array
     */
    public static function where ($list, $properties) {
        return static::filter($list, static::_getListfilter($properties));
    }

    /**
     * Looks through the list and returns the first value that matches all of the key-value pairs listed in properties.
     *
     * @param collection $list The items list
     * @param collection $properties The filter properties
     *
     * @return mixed
     */
    public static function findWhere ($list, $properties) {
        return static::find($list, static::_getListFilter($properties));
    }

    /**
     * Returns the values in list without the elements that the truth test (iterator) passes. The opposite of filter.
     *
     * @param collection  $list     The item list
     * @param callable    $iterator The filtering function
     * @param object     [$context] The object context for filtering function
     *
     * @return array
     */
    public static function reject ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $result = [];
        static::each($list, function ($item, $index) use ($iterator, & $result) {
            if (!$iterator($item))
                $result[$index] = $item;
        });

        return $result;
    }

    /**
     * Alias of Underscore::every
     */
    public static function all ($list, callable $iterator = null, $context = null) {
        return static::every($list, $iterator, $context);
    }

    /**
     * Returns true if all of the values in the list pass the iterator truth test.
     *
     * @param collection  $list      The item list
     * @param callable   [$iterator] The testing function (leave blank for a boolean test)
     * @param object     [$context]  The object context for testing function
     *
     * @return boolean
     */
    public static function every ($list, callable $iterator = null, $context = null) {
        static::_associate($iterator, $context);

        !iterator && $iterator = function ($item) {
            return static::identity($item);
        };

        $result = false;
        static::each($list, function ($item, $index) use ($iterator, & $result) {
            if ($iterator($item)) {
                $result = false;
                return static::BREAKER;
            }
        });

        return $result;
    }

    /**
     * Alias of Underscore::some
     */
    public static function any ($list, callable $iterator = null, $context = null) {
        return static::some($list, $iterator, $context);
    }

    /**
     * Returns true if any of the values in the list pass the iterator truth test. Short-circuits and stops traversing
     * the list if a true element is found.
     *
     * @param collection  $list      The item list
     * @param callable   [$iterator] The testing function (leave blank for a boolean test)
     * @param object     [$context]  The object context for testing function
     *
     * @return boolean
     */
    public static function some ($list, callable $iterator = null, $context = null) {
        !iterator && $iterator = function ($item) {
            return static::identity($item);
        };

        return !static::every($list, function ($item) use ($iterator) {
            return !$iterator($item);
        });
    }

    /**
     * Alias of Underscore::contains
     */
    public static function includes ($list, $value) {
        return static::contains($list, $value);
    }

    /**
     * Returns true if the value is present in the list.
     *
     * @param collection $list    The list
     * @param mixed      $value   The value to search
     * @param boolean   [$strict] Use the strict comparison (===)
     *
     * @return boolean
     */
    public static function contains ($list, $value, $strict = false) {
        return static::some($list, function ($item) use ($value, $strict) {
            return $strict ? $item === $value : $item == $value;
        });
    }

    /**
     * Calls the method named by methodName on each value in the list. Any extra arguments passed to invoke will be
     * forwarded on to the method invocation. If your list items are arrays (instead of objects) methods from
     * ArrayObject can be used (like asort). If the wanted method is not found on the current item during iteration,
     * the item will be left untouched.
     *
     * @param collection   $list       The list
     * @param string       $methodName The method to invoke on each list item
     * @param array      [*$arguments] The method's arguments
     *
     * @return array
     */
    public static function invoke ($list, $methodName, $arguments = array()) {
        if (func_num_args() > 2 && !is_array($arguments))
            $arguments = array_slice(func_get_args(), 2);

        return static::map($list, function ($item) use ($methodName, $arguments) {
            if (is_scalar($item) || is_resource($item))
                return $item;

            if (is_array($item))
                $cast_down = (boolean)$item = new ArrayObject($item);

            if (is_string($methodName) && method_exists($item, $methodName))
                $methodName = [$item, $methodName];
            elseif ($methodName instanceof Closure)
                $methodName = $methodName->bindTo($item);
            else
                return $item;

            call_user_func_array($methodName, $arguments);

            if ($cast_down)
                $item = $item->getArrayCopy();

            return $item;
        });
    }

    /**
     * A convenient version of what is perhaps the most common use-case for map: extracting a list of property values.
     *
     * @param collection $list         The list
     * @param string     $propertyName The property to isolate
     *
     * @return array
     */
    public static function pluck ($list, $propertyName) {
        return static::map($list, function ($item) use ($propertyName) {
            return static::_getItem($item, $propertyName);
        });
    }

    /**
     * Returns the maximum value in list. If iterator is passed, it will be used on each value to generate the
     * criterion by which the value is ranked.
     *
     * @param collection  $list      The list
     * @param callable   [$iterator] The transformation function
     * @param object     [$context]  The context for transformation function
     *
     * @return mixed
     */
    public static function max ($list, callable $iterator = null, $context = null) {
        static::_associate($iterator, $context);

        !$iterator && $iterator = function ($item) {
            return static::identity($item);
        };

        $max = null;
        $result = null;
        static::each($list, function ($item) use ($iterator, & $max, & $result) {
            $num = $iterator($item);
            if (!isset($max) || $num >= $max)
                list($result, $max) = [$item, $num];
        });

        return $result;
    }

    /**
     * Returns the minimum value in list. If iterator is passed, it will be used on each value to generate the
     * criterion by which the value is ranked.
     *
     * @param collection  $list      The list
     * @param callable   [$iterator] The transformation function
     * @param object     [$context]  The context for transformation function
     *
     * @return mixed
     */
    public static function min ($list, callable $iterator = null, $context = null) {
        static::_associate($iterator, $context);

        !$iterator && $iterator = function ($item) {
            return static::identity($item);
        };

        $min = null;
        $result = null;
        static::each($list, function ($item) use ($iterator, & $min, & $result) {
            $num = $iterator($item);
            if (!isset($min) || $num <= $min)
                list($result, $min) = [$item, $num];
        });

        return $result;
    }

    /**
     * Returns a (stably) sorted copy of list, ranked in ascending order by the results of running each value
     * through iterator. Returns NULL in case of error.
     *
     * @param collection  $list     The list to sort
     * @param callable    $iterator The comparison function
     * @param object     [$context] The context object for comparison function
     *
     * @return array
     */
    public static function sortBy ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $result = static::map(static::toArray($list), function ($value, $item) use ($iterator) {
            return compact('value', 'item') + array('criteria' => $iterator($value));
        });

        return uasort($result, function ($left, $right) {
            if ($left['criteria'] == $right['criteria'])
                return 0;

            return $left['criteria'] < $right['criteria'] ? -1 : 1;
        }) ? static::pluck($result, 'value') : null;
    }

    /**
     * Splits a collection into sets, grouped by the result of running each value through iterator. If iterator is a
     * string instead of a function, groups by the property named by iterator on each of the values.
     *
     * @param collection  $list     The list
     * @param callable    $iterator The grouping function
     * @param object     [$context] The context for the grouping function
     *
     * @return array
     */
    public static function groupBy ($list, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $result = [];
        static::each($list, function ($item, $index) use ($iterator, & $result) {
            if (is_string($iterator))
                $result[static::_getItem($item, $iterator)][] = $item;
            else
                $result[$iterator($item)][] = $item;
        });
        return $result;
    }

    /**
     * Sorts a list into groups and returns a count for the number of objects in each group. Similar to groupBy, but
     * instead of returning a list of values, returns a count for the number of values in that group.
     *
     * @param collection  $list     The list
     * @param callable    $iterator The grouping function
     * @param object     [$context] The context for the grouping function
     *
     * @return array
     */
    public static function countBy ($list, callable $iterator, $context = null) {
        return static::map(static::groupBy($list, $iterator, $context), function ($item) {
            return static::size($item);
        });
    }

    /**
     * Returns a shuffled copy of the list
     *
     * @param collection $list The list
     *
     * @return array
     */
    public static function shuffle ($list) {
        $list = static::toArray($list);
        return shuffle($list) ? $list : null;
    }

    /**
     * Creates a real Array from the list (anything that can be iterated over). This method will also accept scalars
     * such as string, number and even null and will *cast* them into arrays, for instance Underscore::toArray(null)
     * is [] altough Underscore::toArray('a') is ['a'].
     *
     * @param mixed *$list The collection or scalar to convert
     *
     * @return array
     */
    public static function toArray ($list) {
        if (func_num_args() > 1)
            return func_get_args();

        if (is_array($list))
            return $list;

        if ($list instanceof ArrayObject || $list instanceof ArrayIterator)
            return $list->getArrayCopy();

        if ($list instanceof Traversable)
            return iterator_to_array($list, true);

        return (array)$list;
    }

    /**
     * Return the number of values in the list. This method will also accept scalars such as string, number and even
     * null or resources but will returns 1 in that case.
     *
     * @param mixed $list The list of items to count
     *
     * @return integer
     */
    public static function size ($list) {
        if (is_scalar($list) || is_resource($list))
            return 1;

        if (is_array($list) || $list instanceof Countable)
            return count($list);

        if ($list instanceof Traversable)
            return iterator_count($list);

        $count = 0;
        static::each($list, function () use (& $count) { $count++; });
        return $count;
    }

    // Array Functions
    // ---------------

    /**
     * Alias of Underscore::first
     */
    public static function head ($array, $n = 1) {
        return static::first($array, $n);
    }

    /**
     * Alias of Underscore::first
     */
    public static function take ($array, $n = 1) {
        return static::first($array, $n);
    }

    /**
     * Returns the first element of an array. Passing n will return the first n elements of the array. Passing guard
     * will force the returned value to be an array.
     *
     * @param collection  $array  The array
     * @param integer    [$n]     The number of items to pick
     * @param boolean    [$guard] Force the return as array
     *
     * @return mixed
     */
    public static function first ($array, $n = 1, $guard = false) {
        if (empty($array))
            return;

        $result = [];
        static::each($array, function ($item) use ($n, & $result) {
            $result[] = $item;

            if (--$n == 0)
                return static::BREAKER;
        });

        return isset($result[1]) || !$guard ? $result : $result[0];
    }

    /**
     * Returns everything but the last entry of the array. Pass n to exclude the last n elements from the result.
     * Passing guard will force the returned value to be an array.
     *
     * @param collection  $array  The array
     * @param integer    [$n]     The number of items to pick
     * @param boolean    [$guard] Force the return as array
     *
     * @return mixed
     */
    public static function initial ($array, $n = 1, $guard = false) {
        return static::first($array, static::size($array) - $n, $guard);
    }

    /**
     * Returns the last element of an array. Passing n will return the last n elements of the array. Passing guard
     * will force the returned value to be an array.
     *
     * @param collection  $array  The array
     * @param integer    [$n]     The number of items to pick
     * @param boolean    [$guard] Force the return as array
     *
     * @return mixed
     */
    public static function last ($array, $n = 1, $guard = false) {
        $array = static::toArray($array);
        $result = array_values(array_slice($array, -$n));
        return isset($result[1]) || !$guard ? $result : $result[0];
    }

    /**
     * Alias of Underscore::rest
     */
    public static function tail ($array, $index = 1, $guard = false) {
        return static::rest($array, $index, $guard);
    }

    /**
     * Alias of Underscore::rest
     */
    public static function drop ($array, $index = 1, $guard = false) {
        return static::rest($array, $index, $guard);
    }

    /**
     * Returns the rest of the elements in an array. Pass an index to return the values of the array from that index
     * onward. Passing guard will force the returned value to be an array.
     *
     * @param collection  $array  The array
     * @param integer    [$index] The position from which the items are picked
     * @param boolean    [$guard] Force the return as array
     *
     * @return mixed
     */
    public static function rest ($array, $index = 1, $guard = false) {
        return static::last($array, -$index, $guard);
    }

    /**
     * Returns a copy of the array with all falsy values removed. In PHP, false, null, 0, "", array() and "0" are all
     * falsy.
     *
     * @param collection $array The array
     *
     * @return array
     */
    public static function compact ($array) {
        return static::filter($array, function ($item) {
            return static::identity($item);
        });
    }

    /**
     * Flattens a nested array (the nesting can be to any depth). If you pass shallow, the array will only be
     * flattened a single level.
     *
     * @param collection  $array    The array
     * @param boolean    [$shallow] False for deep flattening
     *
     * @return array
     */
    public static function flatten ($array, $shallow = false) {
        $output = [];
        $flatten = function($input, $shallow) use (& $output, & $flatten) {
            if ($shallow && static::every($input, ["Underscore", "isArray"]))
                return static::map($input, function ($item) use (& $output) {
                    $output = array_merge($output, array_values($item));
                });

            static::each($input, function ($item) use ($shallow, & $output, & $flatten) {
                if (static::_isSequence($item))
                    $shallow
                        ? ($output = array_merge($output, array_values(static::toArray($item))))
                        : $flatten($item, $shallow);
                else
                    $output[] = $item;
            });

            return $output;
        };

        return $flatten($array, $shallow);
    }

    /**
     * Returns a copy of the array with all instances of the values removed.
     *
     * @param collection  $array  The array
     * @param mixed      *$values The value(s) to strip from the array
     *
     * @return array
     */
    public static function without ($array, $values) {
        if (!is_array($values))
            $values = array_slice(func_get_args(), 1);

        return static::difference($array, $values);
    }

    /**
     * Alias of Underscore::unique
     */
    public static function unique ($array, $isSorted = false, callable $iterator = null, $context = null) {
        return static::uniq($array, $isSorted, $iterator, $context);
    }

    /**
     * Produces a duplicate-free version of the array, using === to test object equality. If you know in advance that
     * the array is sorted, passing true for isSorted will run a much faster algorithm. If you want to compute unique
     * items based on a transformation, pass an iterator function.
     *
     * @param collection  $array     The array
     * @param boolean    [$isSorted] Is array sorted or not ?
     * @param callable   [$iterator] The transformation function
     * @param object     [$context]  The object context for transformation function
     *
     * @return array
     */
    public static function uniq ($array, $isSorted = false, callable $iterator = null, $context = null) {
        $initial = $iterator ? static::map($array, $iterator, $context) : $array;
        $result = [];
        $seen = [];

        static::each($initial, function ($value, $index) use (& $array, $isSorted, & $result, & $seen) {
            if ($isSorted ? (!$index || static::last($seen) !== $value) : !in_array($value, $seen, true)) {
                $seen[] = $value;
                $result[] = $array[$index];
            }
        });

        return $result;
    }

    /**
     * Computes the union of the passed-in arrays: the list of unique items, in order, that are present in one or more
     * of the arrays.
     *
     * @param collection *$array The array(s) to group
     *
     * @return array
     */
    public static function union () {
        return static::unique(static::flatten(func_get_args(), true));
    }

    /**
     * Computes the list of values that are the intersection of all the arrays. Each value in the result is present in
     * each of the arrays.
     *
     * @param collection *$array The arrays(s) to intersect
     *
     * @return array
     */
    public static function intersection () {
        return array_values(call_user_func_array('array_intersect', static::map(func_get_args(), function ($item) {
            return static::toArray($item);
        })));
    }

    /**
     * Similar to without, but returns the values from array that are not present in the other arrays.
     *
     * @param collection  $array  The array
     * @param collection *$others The other(s) array(s)
     *
     * @return array
     */
    public static function difference ($array, $others) {
        return array_values(call_user_func_array('array_diff', static::map(func_get_args(), function ($item) {
            return static::toArray($item);
        })));
    }

    /**
     * Merges together the values of each of the arrays with the values at the corresponding position. Useful when you
     * have separate data sources that are coordinated through matching array indexes.
     *
     * @param collection *$array The array(s) to zip
     *
     * @return array
     */
    public static function zip () {
        $arguments = func_get_args();
        $length = func_num_args();
        $result = [];
        for ($i=0; $i<$length; $i++)
            $result[$i] = static::pluck($arguments, $i);
        return $result;
    }

    /**
     * Converts arrays into objects. Pass either a single list of [key, value] pairs, or a list of keys, and a list of
     * values. If duplicate keys exist, the last value wins.
     *
     * @see http://underscorejs.org/#object
     *
     * @param array  $list The list of keys or pairs
     * @param array [$values] IF $list is an array of keys, then this array is used as values
     *
     * @return object
     */
    public static function obj (array $list, array $values = null) {
        if (!$list)
            return (object)[];

        $result = new stdClass;
        $length = count($list);
        $list = array_values($list);
        for ($i=0; $i<$length; $i++)
            if ($values)
                $result->$list[$i] = $values[$i];
            else
                $result->$list[$i][0] = $list[$i][0];

        return $result;
    }

    /**
     * Returns the index at which value can be found in the array, or -1 if value is not present in the array. This
     * method uses array_search internally and is not optimized for long array binary search
     *
     * @param collection $array The array
     * @param mixed      $item  The item to seek
     *
     * @return scalar
     */
    public static function indexOf ($array, $item) {
        return ($key = array_search($item, static::toArray($array), true)) !== false ? $key : -1;
    }


    /**
     * Returns the index of the last occurrence of value in the array, or -1 if value is not present. This method uses
     * array_search internally and is not optimized for long array binary search
     *
     * @param collection $array The array
     * @param mixed      $item  The item to seek
     *
     * @return scalar
     */
    public static function lastIndexOf ($array, $item) {
        return ($keys = array_keys(static::toArray($array), $item, $strict)) ? array_pop($keys) : -1;
    }

    /**
     * Uses a binary search to determine the index at which the value should be inserted into the list in order
     * to maintain the list's sorted order. If an iterator is passed, it will be used to compute the sort ranking of
     * each value, including the value you pass. Iterator may also be the string name of the property to sort by (eg.
     * length). This method uses array_keys internally and is not optimized for long array binary search.
     *
     * @param collection       $array     The array
     * @param mixed            $value     The value to place
     * @param callable|strign [$iterator] The comparision function or the property name
     * @param object          [$context]  The object context for comparision function
     *
     * @return scalar
     */
    public static function sortedIndex ($array, $value, $iterator = null, $context = null) {
        static::_associate($iterator, $context);

        if (!is_array($array) && !$array instanceof ArrayAccess)
            $array = static::toArray($array);

        $iterator = $iterator === null
            ? function ($item) { return static::identity($item); }
            : static::_getLookupIterator($iterator);

        $value = $iterator($value);
        $low = 0;
        $high = static::size($array);

        while ($low < $high) {
            $mid = ($low + $high) >> 1;
            $iterator($array[$mid]) < $value ? $low = $mid +1 : $high = $mid;
        }

        return $low;
    }

    /**
     * A function to create flexibly-numbered lists of integers, handy for each and map loops. start, if omitted,
     * defaults to 0; step defaults to 1. Returns a list of integers from start to stop, incremented (or decremented)
     * by step, exclusive. This method uses range internally
     *
     * @param integer [$start] The start index (default 0)
     * @param integer  $stop   The end index
     * @param integer [$step]  The iteration steps (default 1)
     *
     * @return array
     */
    public static function range ($start, $stop = null, $step = 1) {
        if (func_num_args() <= 1) {
            $stop = $start ?: 0;
            $start = 0;
        }

        return range($start, $stop, $step);
    }

    // Function (uh, ahem) Functions
    // -----------------------------

    /**
     * Wrap the first function inside of the wrapper function, passing it as the first argument. This allow the
     * wrapper to execute code before and after the function runs, adjust the arguments and execute it conditionnaly.
     * Arguments are passed along to the wrapper function.
     *
     * @param callable $function The function to wrap
     * @param callable $wrapper  The wrapper function
     *
     * @return Closure
     */
    public static function wrap (callable $function, callable $wrapper) {
        return function () use ($function, $wrapper) {
            $args = func_get_args();
            array_unshift($args, $function);
            return call_user_func_array($wrapper, $args);
        };
    }

    /**
     * Returns the composition of a list of functions, where each function consumes the return value of the function
     * that follows. In math terms, composing the functions f(), g(), and h() produces f(g(h())).
     *
     * @param array|callable *$functions The function(s) to compose
     *
     * @return Closure
     */
    public static function compose ($functions) {
        if (!is_array($functions))
            $functions = func_get_args();

        return function () use ($functions) {
            $args = func_get_args();
            foreach ($functions as $function)
                $args = (array)call_user_func_array($function, $args);
            return $args[0];
        };
    }

    /**
     * Creates a version of the function that will only be run after first being called count times. Please note that
     * the function shall not recieve parameters.
     *
     * @param integer  $count    The number of executions required before returning the result
     * @param callable $function The function to be called
     *
     * @return Closure
     */
    public static function after ($count, callable $function) {
        return function () use (& $count, $function) {
            if (--$count > 0)
                return;
            return $function();
        };
    }

    /**
     * Creates a version of the function that can only be called one time. Repeated calls to the modified function
     * will have no effect, returning the value from the original call. Useful for initialization functions, instead
     * of having to set a boolean flag and then check it later.
     *
     * @param callable $function The function to be called once
     *
     * @return Closure
     */
    public static function once (callable $function) {
        return function () use ($function) {
            static $result, $called = false;
            if ($called)
                return $result;
            $called = true;
            return $result = $function();
        };
    }

    /**
     * Partially apply a function by filling in any number of its arguments. Not all the arguments have to be present
     * on the partial construction.
     *
     * @param callable     $function  The function
     * @param array|mixed *$arguments The default arguments
     *
     * @return Closure
     */
    public static function partial (callable $function, $arguments) {
        if (!is_array($arguments))
            $arguments = array_slice(func_get_args(), 1);

        return function () use ($function, $arguments) {
            return call_user_func_array($function, array_merge(func_get_args(), $arguments));
        };
    }

    /**
     * Bind a function to an object, meaning that whenever the function is called, the value of this will be the
     * object. Optionally, pass arguments to the function to pre-fill them, also known as partial application.
     *
     * @param Closure       $function   The function to bind
     * @param object        $object     The object to bind the function to
     * @param array|mixed [*$arguments] The arguments
     *
     * @return Closure
     */
    public static function bind (callable $function, $object) {
        static::_associate($function, $object);
        return static::partial($function, array_slice(func_get_args(), 2));
    }

    /**
     * Binds a number of methods on the object, specified by methodNames, to be run in the context of that object
     * whenever they are invoked. Very handy for binding functions that are going to be used as event handlers, which
     * would otherwise be invoked with a fairly useless this. methodNames are required. Keep in mind PHP doesn't
     * allow to call a closure property value like a method, for instance $o->myClosure(), given $o is an instance of
     * stdClass, won't work.
     *
     * @param object        $object      The object
     * @param array|scalar *$methodNames The methods' names to bind
     *
     * @return object
     */
    public static function bindAll ($object, $methodNames) {
        if (!is_array($methodNames))
            $methodNames = array_slice(func_get_args(), 1);

        foreach ($methodNames as $methodName)
            if (isset($object->$methodName))
                $object->$methodName = static::bind($object->$methodName, $object);

        return $object;
    }

    /**
     * Memoizes a given function by caching the computed result. Useful for speeding up slow-running computations. If
     * passed an optional hashFunction, it will be used to compute the hash key for storing the result, based on the
     * arguments to the original function. The default hashFunction just uses the first argument to the memoized
     * function as the key.
     *
     * @param callable  $function      The function to memoize
     * @param callable [$hashFunction] The function that generates the hash using the $function's arguments
     *
     * @return Closure
     */
    public static function memoize (callable $function, callable $hashFunction = null) {
        $hashFunction = $hashFunction ?: function ($arguments) {
            return array_shift($arguments);
        };

        return function () use ($function, $hashFunction) {
            static $cache = [];

            $args = func_get_args();
            $ckey = $hashFunction($args);

            return isset($cache[$ckey])
                ? $cache[$ckey]
                : $cache[$ckey] = call_user_func_array($function, $args);
        };
    }

    /**
     * Creates and returns a new, throttled version of the passed function, that, when invoked repeatedly, will only
     * actually call the original function at most once per every wait milliseconds. Useful for rate-limiting events
     * that occur faster than you can keep up with.
     *
     * @param callable $function The function to throttle
     * @param integer  $wait     The time to wait between calls (in milliseconds)
     *
     * @return Closure
     */
    public static function throttle (callable $function, $wait) {
        return function () use ($function, $wait) {
            static $pretime;

            $curtime = microtime(true);
            if (!$pretime || ($curtime - $pretime) >= ($wait / 1000)) {
                $pretime = $curtime;
                $function();
            }
        };
    }

    // Object Functions
    // ----------------

    /**
     * Retrieve all the names of the object's properties.
     *
     * @param collection $object
     *
     * @return array
     */
    public static function keys ($object) {
        if ($object instanceof stdClass)
            $object = (array)$object;

        if (is_array($object))
            return array_keys($object);

        $result = [];
        static::each($object, function ($item, $key) use (& $result) {
            $result[] = $key;
        });

        return $result;
    }

    /**
     * Return all of the values of the object's properties.
     *
     * @param collection $object
     *
     * @return array
     */
    public static function values ($object) {
        if ($object instanceof stdClass)
            $object = (array)$object;

        if (is_array($object))
            return array_values($object);

        $result = [];
        static::each($object, function ($item) use (& $result) {
            $result[] = $item;
        });

        return $result;
    }

    /**
     * Convert an object into a list of [key, value] pairs.
     *
     * @param collection $object
     *
     * @return array
     */
    public static function pairs ($object) {
        $result = [];
        static::each($object, function ($item, $key) use (& $result) {
            $result[] = [$key, $item];
        });

        return $result;
    }

    /**
     * Returns a copy of the object where the keys have become the values and the values the keys. For this to work,
     * all of your object's values should be unique and string serializable.
     *
     * @param collection $object
     *
     * @return array|object
     */
    public static function invert ($object) {
        if (!is_array($object))
            $castDown = (boolean)$object = static::toArray($object);

        return !empty($castDown) ? (object)array_flip($object) : array_flip($object);
    }

    /**
     * Alias of Underscore::functions
     */
    public static function methods ($object) {
        return static::functions($object);
    }

    /**
     * Returns a sorted list of the names of every method in an object — that is to say, the name of every function
     * property of the object.
     *
     * @param collection $object
     *
     * @return array
     */
    public static function functions ($object) {
        return static::filter($object, function ($item) {
            return static::isFunction($item);
        });
    }

    /**
     * Copy all of the properties in the source objects over to the destination object, and return the destination
     * object. It's in-order, so the last source will override properties of the same name in previous arguments.
     *
     * @param collection  $destination The destination object
     * @param collection *$sources     The source(s) object
     *
     * @return collection
     */
    public static function extend ($destination, $sources) {
        $sources = array_slice(func_get_args(), 1);

        $result = $destination;
        static::each($sources, function ($source) use (& $result) {
            static::each($source, function ($value, $name) use (& $result) {
                static::_setItem($result, $name, $value);
            });
        });

        return $destination;
    }

    /**
     * Return a copy of the object, filtered to only have values for the whitelisted keys (or array of valid keys).
     *
     * @param collection  $object The object to pick members on
     * @param collection *$keys   The member's names to pick
     *
     * @return array|object
     */
    public static function pick ($object, $keys) {
        if (!is_array($keys))
            $keys = array_slice(func_get_args(), 1);

        $result = [];
        static::each($keys, function ($name) use ($object, & $result) {
            $result[$name] = static::has($object, $name)
                ? static::_getItem($object, $name)
                : null;
        });

        return is_array($object) ? $result : (object)$result;
    }

    /**
     * Return a copy of the object, filtered to omit the blacklisted keys (or array of keys).
     *
     * @param object        $object The object to omit members from
     * @param array|scalar *$keys   The member's names to omit
     *
     * @return object
     */
    public static function omit ($object, $keys) {
        if (!is_array($keys))
            $keys = array_slice(func_get_args(), 1);

        $result  = new stdClass;
        $keys = array_flip(static::toArray($keys));
        static::each($object, function ($value, $name) use ($keys, & $result) {
            if (!isset($key[$name]))
                $result->$name = $value;
        });

        return $result;
    }

    /**
     * Fill in null properties in object with values from the defaults objects, and return the object. As soon as the
     * property is filled, further defaults will have no effect.
     *
     * @param collectiion  $object   The object to fill
     * @param collection  *$defaults The defaults object(s)
     *
     * @return object|array
     */
    public static function defaults ($object, $defaults) {
        $defaults = array_slice(func_get_args(), 1);

        if ($object instanceof stdClass) {
            $object = (array)$object;
            $castDown = true;
        }

        $result = $object;
        $isArray = is_array($object);
        static::each($defaults, function ($default) use (& $result, $isArray) {
            if (!static::_isSequence($default))
                throw new InvalidArgumentException("cannot use " . static::_getType($default) . " as default");

            if ($isArray)
                return $result += static::toArray($default);

            static::each($default, function ($item, $index) use (& $result) {
                if (!static::has($result, $index))
                    static::_setItem($result, $index, $item);
            });
        });

        return !empty($castDown) ? (object)$result : $result;
    }

    /**
     * Alias of Underscore::duplicate
     */
    public static function copy ($object) {
        return static::duplicate($object);
    }

    /**
     * Create a shallow-copied clone of the object. Any nested objects or arrays will be copied by reference, not
     * duplicated. This method is safe to use with arrays.
     *
     * @see http://underscorejs.org/#clone
     *
     * @param collection $object The object to clone
     *
     * @return collection
     */
    public static function duplicate ($object) {
        if (is_object($object))
            return clone $object;

        return static::extend([], $object);
    }

    /**
     * Invokes interceptor with the object, and then returns object. The primary purpose of this method is to "tap
     * into" a method chain, in order to perform operations on intermediate results within the chain.
     *
     * @param collection $object      The object
     * @param callable   $interceptor The interception function
     *
     * @return collection
     */
    public static function tap ($object, callable $interceptor) {
        $interceptor($object);
        return $object;
    }

    /**
     * Does the object contain the given key?
     *
     * @param collection $object The object
     * @param string     $key    The key to test
     *
     * @return boolean
     */
    public static function has ($object, $key) {
        return static::_hasItem($object, $key);
    }

    /**
     * Performs an optimized deep comparison between the two objects, to determine if they should be considered equal.
     *
     * @param collection $object The object
     * @param collection $other  The other object to compare the first with
     *
     * @return boolean
     */
    public static function isEqual ($object, $other) {
        return static::_equal($object, $other, [], []);
    }

    /**
     * Returns true if object contains no values (no enumerable own-properties). Works with scalars as well.
     *
     * @param collection $object The object
     *
     * @return boolean
     */
    public static function isEmpty ($object) {
        if (empty($object))
            return true;

        return static::size($object) === 0;
    }

    /**
     * Returns true if $object is an array or an object implementing ArrayAccess
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isArray ($object) {
        return is_array($object) || $object instanceof ArrayAccess;
    }

    /**
     * Returns true if value is an Object.
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isObject ($object) {
        return is_object($object);
    }

    /**
     * Returns true if object is a Function.
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isFunction ($object) {
        return is_callable($object);
    }

    /**
     * Returns true of object is a Number.
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isNumber ($object) {
        return is_numeric($object) || $object instanceof SplInt || $object instanceof SplFloat;
    }

    /**
     * Returns true if object is a String.
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isString ($object) {
        return is_string($object) || $object instanceof SplString;
    }

    /**
     * Returns true if object is a DateTime instance.
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isDate ($object) {
        return $object instanceof DateTime;
    }

    /**
     * Returns true if object is a valid regular expression (PCRE).
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isRegExp ($object) {
        return static::isString($object) && (@preg_match($object, null) !== false);
    }

    /**
     * Returns true if object is a finite number
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isFinite ($object) {
        return is_finite($object);
    }

    /**
     * Returns true if object is NaN (Not a Number).
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isNaN ($object) {
        return is_nan($object);
    }

    /**
     * Returns true if object is a Boolean
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isBoolean ($object) {
        return is_bool($object) || $object instanceof SplBool;
    }

    /**
     * Returns true if object is Null
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isNull ($object) {
        return is_null($object);
    }

    /**
     * Returns true if $object is a scalar
     *
     * @param mixed $object The object to test
     *
     * @return boolean
     */
    public static function isScalar ($object) {
        return is_scalar($object) || $object instanceof SplType;
    }

    // Utility Functions
    // -----------------

    /**
     * Returns the same value that is used as the argument
     *
     * In math: f(x) = x. This function looks useless, but is used throughout Underscore as a default iterator.
     *
     * @param mixed $value The value
     *
     * @return mixed
     */
    public static function identity ($value) {
        return $value;
    }

    /**
     * Invokes the given iterator function n times. Each invocation of iterator is called with an index argument.
     * Produces an array of the returned values.
     *
     * @param integer   $n        The number of time iterator will be called
     * @param callable  $iterator The iterator
     * @param object   [$context] The context object for iterator
     *
     * @return array
     */
    public static function times ($n, callable $iterator, $context = null) {
        static::_associate($iterator, $context);

        $n = max(0, $n);
        $accum = [];
        for ($i=0; $i<$n; $i++)
            $accum[] = $iterator($i);

        return $accum;
    }

    /**
     * Returns a random integer between min and max, inclusive. If you only pass one argument, it will return a number
     * between 0 and that number.
     *
     * @param integer [$min] The min value
     * @param integer  $max  The max value
     *
     * @return integer
     */
    public static function random ($min, $max = null) {
        if ($max === null)
            list($min, $max) = [0, $min];

        return rand($min, $max);
    }

    /**
     * Allows you to extend Underscore with your own utility functions. Pass a hash of array('name' => function)
     * definitions to have your functions added to the Underscore library, as well as the OOP wrapper.
     *
     * @param array $functions The functions to add to Underscore
     *
     * @return void
     */
    public static function mixin (array $functions) {
        static::$_userFunctions = $functions + static::$_userFunctions;
    }


    /**
     * Returns callable version of any Underscore method (event the user defined ones).
     *
     * @param string *$method The method name
     *
     * @return Closure|array
     */
    public static function provide ($method) {
        $class = get_called_class();
        return func_num_args() == 1
            ? function () use ($class, $method) {
                return call_user_func_array([$class, $method], func_get_args());
            }
            : static::map(func_get_args(), function ($method) {
                return static::provide($method);
            });
    }

    /**
     * Generate a pseudo-unique id.
     *
     * @param string [$prefix] The prefix for generated id
     *
     * @return string
     */
    public static function uniqueId ($prefix = "") {
        return uniqid($prefix);
    }

    /**
     * Escapes a string for insertion into HTML, replacing &, <, >, ", ', and / characters.
     *
     * @param string $string The string to escape
     *
     * @return string
     */
    public static function escape ($string) {
        return htmlentities((string)$string);
    }

    /**
     * The opposite of escape, replaces &amp;, &lt;, &gt;, &quot;, &#x27;, and &#x2F; with their unescaped
     * counterparts.
     *
     * @param string $string The string to unescape
     *
     * @return string
     */
    public static function unescape ($string) {
        return html_entity_decode((string)$string);
    }

    /**
     * If the value of the named property is a function then invoke it with the object as context; otherwise, return
     * it.
     *
     * @param collection $object   The object
     * @param string     $property The property or method name
     *
     * @return mixed
     */
    public static function result ($object, $property) {
        $value = static::_getItem($object, $property);

        if (static::isFunction($value)) {
            static::_associate($value, $object);
            return $value();
        }

        return $value;
    }

    /**
     * By default, Underscore uses ERB-style template delimiters, change the following template settings to
     * use alternative delimiters.
     *
     * @var array
     */
    public static $templateSettings = [
        'evaluate'    => '/<%([\s\S]+?)%>/',
        'interpolate' => '/<%=([\s\S]+?)%>/',
        'escape'      => '/<%-([\s\S]+?)%>/',
    ];

    /**
     * When customizing templateSettings, if you don't want to define an interpolation, evaluation or escaping
     * regex, we need one that is guaranteed not to match.
     *
     * @var string
     */
    protected static $_noMatch = "/(.)^/";

    /**
     * Compiles PHP templates into functions that can be evaluated for rendering. Useful for rendering complicated
     * bits of HTML from JSON data sources. Template functions can both interpolate variables, using <%= ... %>, as
     * well as execute arbitrary PHP code, with <% ... %>. If you wish to interpolate a value, and have it be
     * HTML-escaped, use <%- ... %> When you evaluate a template function, pass in a data object that has properties
     * corresponding to the template's free variables. If you're writing a one-off, you can pass the data object as
     * the second parameter to template in order to render immediately instead of returning a template function. The
     * settings argument should be a hash containing any Underscore::$templateSettings that should be overridden.
     *
     * If ERB-style delimiters aren't your cup of tea, you can change Underscore's template settings to use different
     * symbols to set off interpolated code. Define an interpolate regex to match expressions that should be
     * interpolated verbatim, an escape regex to match expressions that should be inserted after being HTML escaped,
     * and an evaluate regex to match expressions that should be evaluated without insertion into the resulting string.
     * You may define or omit any combination of the three.
     *
     * @param string      $templateString The template string
     * @param collection [$data]          The template variables
     * @param collection [$settings]      The template settings (regexes for evaluation, interpolation and escaping)
     *
     * @return string|callable
     */
    public static function template ($templateString, $data = [], $settings = []) {
        $settings = static::defaults([], $settings, static::$templateSettings, [
            'evaluate'    => static::$_noMatch,
            'interpolate' => static::$_noMatch,
            'escape'      => static::$_noMatch,
        ]);

        $class = get_called_class();

        $pattern = '~' . implode('|', static::map(
            static::pick($settings, 'escape', 'interpolate', 'evaluate'),
            function ($subpattern, $name) {
                return "(?<$name>" . trim($subpattern, $subpattern[0]) . ")";
            }
        )) . '|$~';

        $templateString = preg_replace_callback($pattern, function ($match) use ($class) {
            if ($match['escape'])
                return sprintf('<?php echo %s::escape(%s) ?>', $class, trim($match[2]));

            if ($match['interpolate'])
                return sprintf('<?php echo %s ?>', trim($match[4]));

            if ($match['evaluate'])
                return sprintf('<?php %s ?>', trim($match[6]));
        }, $templateString);

        $templateFunction = create_function(
            '$data',
            'extract($data); ob_start(); ?>'. $templateString . '<?php return ob_get_clean();'
        );

        return $data ? $templateFunction(static::toArray($data)) : $templateFunction;
    }

    // Chaining
    // --------

    /**
     * Returns a wrapped object. Calling methods on this object will continue to return wrapped objects until value
     * is used. Calling chain will cause all future method calls to return wrapped objects. When you've finished the
     * computation, use value to retrieve the final value.
     *
     * @param collection $object The object to chain
     *
     * @return Chain
     */
    public static function chain ($object) {
        $bridge = new Bridge($object, new static);
        $bridge->chain = true;
        return $bridge;
    }

    // Internal Functions
    // ------------------

    /**
     * User defined functions internal hashmap
     *
     * @internal
     * @var array
     */
    protected static $_userFunctions = [];

    /**
     * Allows you to call the user defined functions registered with Underscore::mixin
     *
     * @internal
     *
     * @param string $method    The user defined function name
     * @param array  $arguments Function's arguments
     *
     * @return mixed
     */
    public static function __callStatic ($method, array $arguments) {
        if (!isset(static::$_userFunctions[$method]))
            throw new BadMethodCallException("no such method $method");

        return call_user_func_array(static::$_userFunctions[$method], $arguments);
    }

    /**
     * Allows object calls toward user defined static methods
     */
    public function __call ($method, array $arguments) {
        return static::__callStatic($method, $arguments);
    }

    /**
     * Gets the native type or the classname of given value
     *
     * @param mixed $value The value
     *
     * @return string
     */
    protected static function _getType ($value) {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    /**
     * Tells if the value is a sequence
     *
     * A sequence is either an array, a Traversable instance or a stdClass instance.
     *
     * @param mixed $value The value
     *
     * @return boolean
     */
    protected static function _isSequence ($value) {
        return is_array($value) || $value instanceof Traversable || $value instanceof stdClass;
    }

    /**
     * Safely Associates the given function with the given context
     *
     * @param callable &$function The function to associate
     * @param object    $context  The object context for function
     *
     * @return void
     */
    protected static function _associate (& $function, $context) {
        if ($context && $function instanceof Closure && PHP_VERSION_ID >= 50400)
            $function = $function->bindTo($context);
    }

    /**
     * Returns an iterator that matches a list item against properties
     *
     * @internal
     *
     * @param array|object $properties The filter properties
     *
     * @return Closure
     */
    protected static function _getListfilter ($properties) {
        return function ($item) use ($properties) {
            foreach ($properties as $property => $value)
                if (static::_getItem($item, $property) != $value)
                    return false;
            return true;
        };
    }

    /**
     * An internal function to generate lookup iterators
     *
     * @param string|callable $value The iterator
     *
     * @return callable
     */
    protected static function _getLookupIterator ($value) {
        return is_callable($value) ? $value : function ($object) use ($value) {
            return static::_getItem($object, $value);
        };
    }

    /**
     * Tells if the given structure has the property identified by name
     *
     * Gives priority to array's getters ([] priorityze on ->)
     *
     * @warning If name is an object property defined but not initialized, it returns false. Remember that in PHP,
     * 'null' is equivalent to 'undefined'.
     *
     * @param array|object $structure The structure
     * @param string       $name      The property name
     *
     * @return boolean
     */
    protected static function _hasItem ($structure, $name) {
        if ((is_array($structure) || $structure instanceof ArrayAccess) && isset($structure[$name]))
            return true;
        if (is_object($structure) && isset($structure->$name))
            return true;
        return false;
    }

    /**
     * Returns the given structure's item identfied by its name
     *
     * Gives priority to array's getters ([] priorityze on ->)
     *
     * @internal
     *
     * @param array|object $structure The structure that holds the item
     * @param string       $name      The name of property or index to look for
     *
     * @return mixed
     */
    protected static function _getItem ($structure, $name) {
        if ((is_array($structure) || $structure instanceof ArrayAccess) && isset($structure[$name]))
            return $structure[$name];
        if (is_object($structure) && isset($structure->$name))
            return $structure->$name;
        return null;
    }

    /**
     * Sets the given structure's item identified by its name
     *
     * Gives priority to array's getters ([] priorityze on ->)
     *
     * @internal
     *
     * @param array|object $structure The structure
     * @param string       $name      The name of property or index to set
     * @param mixed        $value     The value
     *
     * @return void
     */
    protected static function _setItem ($structure, $name, $value) {
        if (is_array($structure) || $structure instanceof ArrayAccess)
            $structure[$name] = $value;
        if (is_object($structure))
            $structure->$name = $value;
    }

    /**
     * Tells if two objects are equals.
     *
     * @param mixed $a The
     */
    protected static function _equal ($a, $b, $aStack, $bStack) {
        // Unwrap any wrapped object.
        $a instanceof self && $a = $a->_wrapped;
        $b instanceof self && $b = $b->_wrapped;

        if ($a === null && $b === null)
            return true;

        // Two resources are always considered different since there is no way to compare them
        if (is_resource($a) && is_resource($b))
            return false;

        // Perform classic scalar comparison.
        if (is_scalar($a) && is_scalar($b))
            return $a == $b;

        // Assume equality for cyclic structures. The algorithm for detecting cyclic structures is adapted from
        // ES 5.1 section 15.12.3, abstract operation JO.
        $lenght = count($aStack);
        while ($length--)
            if ($aStack[$length] == $a)
                return $bStack[$length] == $b;

        // Add the first object to the stack of traversed objects.
        $aStack[] = $a;
        $bStack[] = $b;

        $result = true;
        $size = 0;

        if (static::_isSequence($a) && static::_isSequence($b)) {
            // Try to deep compare sequences.
            static::each($a, function ($item, $key) use (& $a, & $b, & $aStack, & $bStack, & $size, & $result) {
                $size++;

                $result = static::has($b, $key) && static::_equal(
                    static::_getItem($a, $key),
                    static::_getItem($b, $key),
                    $aStack,
                    $bStack
                );

                if (!$result)
                    return static::BREAKER;
            });

            $result = !($size - static::size($b));
        }
        else {
            // Last hope comparison.
            $result = $a == $b;
        }

        // Remove the first object from the stack of traversed objects.
        array_pop($aStack);
        array_pop($aStack);

        return $result;
    }
}