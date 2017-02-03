<?php

namespace Underscore;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use BadMethodCallException;
use Closure;
use Countable;
use Underscore\Exception\BreakException;
use Underscore\Exception\ContinueException;
use Generator;
use Iterator;
use RuntimeException;
use SplFloat;
use SplInt;
use SplString;
use SplType;
use stdClass;
use Traversable;
use UnexpectedValueException;

/**
 * Underscore.php
 * https://github.com/bdelespierre/underscore.php
 * (c) 2013-2017 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 */
class Underscore
{
    const VERSION = "0.3.0";

    /**
     * Collection Functions
     * --------------------
     */

    /**
     * Iterates over a list of elements, yielding each in turn to an iterator function. The iterator is bound to
     * the context object, if one is passed. Each invocation of iterator is called with three arguments:
     * (element, index, list). If list is an object, iterator's arguments will be (value, key, list).
     *
     * @category Collection Functions
     *
     * @param traversable $list the list to iterate over
     * @param callable $iterator the iteration function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @throws InvalidArgumentException when $list cannot be iterated over
     *
     * @return void
     */
    public static function each(iterable $list, callable $iterator, $context = null): iterable
    {
        if (empty($list)) {
            return $list;
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        foreach ($list as $index => $item) {
            try {
                $iterator($item, $index, $list);
            } catch (BreakException $e) {
                break;
            } catch (ContinueException $e) {
                continue;
            }
        }

        return $list;
    }

    /**
     * Breaks curent loop iteration
     *
     * @throws BreakException
     *
     * @return void
     */
    public static function break()
    {
        throw new BreakException;
    }

    /**
     * Continues current loop iteration
     *
     * @throws ContinueException
     *
     * @return void
     */
    public static function continue()
    {
        throw new ContinueException;
    }

    /**
     * @see Underscore::eachReference
     */
    public static function walk(iterable &$list, callable $iterator, $context = null): iterable
    {
        return static::eachReference($list, $iterator, $context);
    }

    /**
     * Does the very same job as each but provide a reference of every list item to the iterator function.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list to iterate over
     * @param callable $iterator the iteration function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @throws UnexpectedValueException if $list is an instance of Iterator
     *
     * @return void
     */
    public static function eachReference(iterable &$list, callable $iterator, $context = null): iterable
    {
        if ($list === null) {
            return $list;
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if (is_array($list)) {
            try {
                array_walk($list, $iterator, $list);
            } catch (BreakException $e) {
                // noop
            } catch (ContinueException $e) {
                throw new RuntimeException("cannot continue here", 0, $e);
            }

            return $list;
        }

        if ($list instanceof Iterator) {
            throw new UnexpectedValueException("cannot iterate over an iterator by reference");
        }

        foreach ($list as $index => &$item) {
            try {
                $iterator($item, $index, $list);
            } catch (BreakException $e) {
                break;
            } catch (ContinueException $e) {
                continue;
            }
        }

        return $list;
    }

    /**
     * @see Underscore::eachReferenceRecursive
     */
    public static function walkRecursive(iterable &$list, callable $iterator, $context = null): iterable
    {
        return static::eachReferenceRecursive($list, $iterator, $context);
    }

    /**
     * Same as eachReference but iterates recursively on the list
     *
     * @since 0.3.0
     * @todo write tests
     * @category Collection Functions
     *
     * @param  iterable    &$list    the list
     * @param  callable    $iterator the iterator function
     * @param  object      $context  the context, if any
     * @param  int|integer $level    @internal do NOT set this value
     *
     * @throws UnexpectedValueException if the list, or any of its children, is an iterator
     *
     * @return iterable
     */
    public static function eachReferenceRecursive(
        iterable &$list,
        callable $iterator,
        $context = null,
        int $level = 0
    ): iterable {
        if ($list === null) {
            return $list;
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if (is_array($list)) {
            try {
                array_walk_recursive($list, $iterator, $list);
            } catch (BreakException $e) {
                // noop
            } catch (ContinueException $e) {
                throw new RuntimeException("cannot continue here", 0, $e);
            }

            return $list;
        }

        if ($list instanceof Iterator) {
            throw new UnexpectedValueException("cannot iterate over an iterator by reference");
        }

        foreach ($list as $item => &$item) {
            try {
                static::isTraversable($item)
                    ? static::eachReferenceRecursive($item, $iterator, null, $level +1)
                    : $iterator($item, $index, $list);
            } catch (BreakException $e) {
                if ($level) {
                    throw $e;
                }

                break;
            } catch (ContinueException $e) {
                continue;
            }
        }

        return $list;
    }

    /**
     * @see Underscore::map
     */
    public static function collect(iterable $list, callable $iterator, $context = null): array
    {
        return static::map($list, $iterator, $context);
    }

    /**
     * Produces a new array of values by mapping each value in list through a transformation function (iterator). The
     * iterator is bound to the context object, if one is passed. Each invocation of iterator is called with three
     * arguments: (element, index, list). If list is an object, iterator's arguments will be (value, key, list).
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to map
     * @param callable $iterator the transformation function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function map(iterable $list, callable $iterator, $context = null): array
    {
        if ($context !== null) {
            $iterator = self::bind($iterator, $context);
        }

        $result = [];
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            $result[$index] = $iterator($item, $index, $list);
        });

        return $result;
    }

    /**
     * @see Underscore::reduce
     */
    public static function inject(iterable $list, callable $iterator, $memo, $context = null)
    {
        return static::reduce($list, $iterator, $memo, $context);
    }

    /**
     * @see Underscore::reduce
     */
    public static function foldl(iterable $list, callable $iterator, $memo, $context = null)
    {
        return static::reduce($list, $iterator, $memo, $context);
    }

    /**
     * Also known as inject and foldl, reduce boils down a list of values into a single value. Memo is the initial
     * state of the reduction, and each successive step of it should be returned by iterator. The iterator is passed
     * four arguments: the memo, then the value and index (or key) of the iteration, and finally a reference to
     * the entire list.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to reduce
     * @param callable $iterator the reduction function
     * @param mixed $memo The initial reduction state
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return mixed
     */
    public static function reduce(iterable $list, callable $iterator, $memo, $context = null)
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        static::each($list, function ($item, $index, $list) use ($iterator, & $memo) {
            $memo = $iterator($memo, $item, $index, $list);
        });

        return $memo;
    }

    /**
     * @see Underscore::reduceRight
     */
    public static function foldr(iterable $list, callable $iterator, $memo, $context = null)
    {
        return static::reduceRight($list, $iterator, $memo, $context);
    }

    /**
     * The right-associative version of reduce.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to reduce
     * @param callable $iterator the reduction function
     * @param mixed $memo The initial reduction state
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return mixed
     */
    public static function reduceRight(iterable $list, callable $iterator, $memo, $context = null)
    {
        return static::reduce(array_reverse(static::toArray($list)), $iterator, $memo, $context);
    }

    /**
     * @see Underscore::find
     */
    public static function detect(iterable $list, callable $iterator, $context = null)
    {
        return static::find($list, $iterator, $context);
    }

    /**
     * Looks through each value in the list, returning the first one that passes a truth test (iterator), or null if
     * no value passes the test. The function returns as soon as it finds an acceptable element, and doesn't traverse
     * the entire list.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to iterate over
     * @param callable $iterator the truth-test function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return mixed
     */
    public static function find(iterable $list, callable $iterator, $context = null)
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        $result = null;
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            if ($iterator($item, $index, $list)) {
                $result = $item;
                throw new BreakException;
            }
        });

        return $result;
    }

    /**
     * @see Underscore::filter
     */
    public static function select(iterable $list, callable $iterator, $context = null): array
    {
        return static::filter($list, $iterator, $context);
    }

    /**
     * Looks through each value in the list, returning an array of all the values that pass a truth test (iterator). If
     * iterator isn't provided, each value will be evaluated as a boolean.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to filter
     * @param callable $iterator the filtering function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function filter(iterable $list, callable $iterator = null, $context = null): array
    {
        if (empty($list)) {
            return [];
        }

        if ($iterator === null && $context === null && is_array($list)) {
            return array_filter($list);
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if ($iterator === null) {
            $iterator = [static::class, 'identity'];
        }

        $result = [];
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            if ($iterator($item, $index, $list)) {
                $result[$index] = $item;
            }
        });

        return $result;
    }

    /**
     * Looks through each value in the list, returning an array of all the values that contain all of the key-value
     * pairs listed in properties.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to filter
     * @param iterable $properties the key-values pairs each filtered item must match
     *
     * @return array
     */
    public static function where(iterable $list, iterable $properties): array
    {
        return static::filter($list, static::getListfilter($properties));
    }

    /**
     * Looks through the list and returns the first value that matches all of the key-value pairs listed in properties.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to filter
     * @param iterable $properties the key-values pairs each filtered item must match
     *
     * @return mixed
     */
    public static function findWhere(iterabel $list, iterable $properties)
    {
        return static::find($list, static::getListFilter($properties));
    }

    /**
     * Returns the values in list without the elements that the truth test (iterator) passes. The opposite of filter.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to filter
     * @param callable $iterator the truth-test function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function reject(iterable $list, callable $iterator, $context = null): array
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        $result = [];
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            if (!$iterator($item, $index, $list)) {
                $result[$index] = $item;
            }
        });

        return $result;
    }

    /**
     * @see Underscore::every
     */
    public static function all(iterable $list, callable $iterator = null, $context = null): bool
    {
        return static::every($list, $iterator, $context);
    }

    /**
     * Returns true if all of the values in the list pass the iterator truth test. Short-circuits and stops traversing
     * the list if a false element is found.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to filter
     * @param callable $iterator the truth-test function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return bool
     */
    public static function every(iterable $list, callable $iterator = null, $context = null): bool
    {
        if ($iterator === null && $context === null && is_array($list)) {
            return count(array_keys($list, true)) == count($list);
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if ($iterator === null) {
            $iterator = [static::class, 'identity'];
        }

        $result = true;
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            if (!$iterator($item, $index, $list)) {
                $result = false;
                throw new BreakException;
            }
        });

        return $result;
    }

    /**
     * @see Underscore::some
     */
    public static function any(iterable $list, callable $iterator = null, $context = null): bool
    {
        return static::some($list, $iterator, $context);
    }

    /**
     * Returns true if any of the values in the list pass the iterator truth test. Short-circuits and stops traversing
     * the list if a true element is found.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to filter
     * @param callable $iterator the truth-test function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return bool
     */
    public static function some(iterable $list, callable $iterator = null, $context = null): bool
    {
        if ($iterator === null && $context === null && is_array($list)) {
            return false !== array_search(true, $list);
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if ($iterator === null) {
            $iterator = [static::class, 'identity'];
        }

        return !static::every($list, function ($item, $index, $list) use ($iterator) {
            return !$iterator($item, $index, $list);
        });
    }

    /**
     * @see Underscore::contains
     */
    public static function includes(iterable $list, $value, bool $strict = false): boolean
    {
        return static::contains($list, $value, $strict);
    }

    /**
     * Returns true if the value is present in the list.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items
     * @param mixed $value the value to look for
     * @param boolean $strict type of value is also used in coparision
     *
     * @return array
     */
    public static function contains(iterable $list, $value, bool $strict = false): boolean
    {
        if (is_array($list)) {
            return false !== array_search($value, $list, $strict);
        }

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
     * @category Collection Functions
     *
     * @param traversable $list the list of items to invoke method/function onto
     * @param callable,string $methodName the name of the method to invoke or a closure
     * @param array $arguments the method's arguments
     *
     * @return array
     */
    public static function invoke(iterable $list, $methodName, ...$arguments): array
    {
        if (!is_string($methodName) && !is_callable($methodName)) {
            throw new UnexpectedValueException("methodName is expected to be string or callable");
        }

        return static::map($list, function ($item) use ($methodName, $arguments) {
            if (is_scalar($item) || is_resource($item)) {
                return $item;
            }

            if ($cast = is_array($item)) {
                $item = new ArrayObject($item);
            }

            switch (true) {
                case is_string($methodName) && method_exists($item, $methodName):
                    $methodName = [$item, $methodName];
                    break;

                case $methodName instanceof Closure:
                    $methodName = static::bind($methodName, $item);
                    break;

                default:
                    return $item;
            }

            $methodName(...$arguments);
            return $cast ? $item->getArrayCopy() : $item;
        });
    }

    /**
     * A convenient version of what is perhaps the most common use-case for map: extracting a list of property values.
     *
     * @category Collection Functions
     *
     * @param traversable $list the list of items
     * @param string $propertyName the name of the property to extract from each item
     *
     * @return array
     */
    public static function pluck(iterable $list, string $propertyName): array
    {
        if (is_array($list)) {
            return array_column($list, $propertyName);
        }

        return static::map($list, function ($item) use ($propertyName) {
            return static::get($item, $propertyName);
        });
    }

    /**
     * Returns the maximum value in list. If iterator is passed, it will be used on each value to generate the
     * criterion by which the value is ranked.
     *
     * @category Collection Functions
     *
     * @param traversable $list the list of items
     * @param callable $iterator optional, the comparision function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return mixed
     */
    public static function max(iterable $list, callable $iterator = null, $context = null)
    {
        if ($iterator === null && $context === null && is_array($list)) {
            return max(...$list);
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if ($iterator === null) {
            $iterator = [static::class, 'identity'];
        }

        $max = null;
        $result = null;
        static::each($list, function ($item, $index, $list) use ($iterator, &$max, &$result) {
            if (!isset($max) || ($num = $iterator($item, $index, $list)) > $max) {
                $result = $item;
                $max = $num;
            }
        });

        return $result;
    }

    /**
     * Returns the minimum value in list. If iterator is passed, it will be used on each value to generate the
     * criterion by which the value is ranked.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items
     * @param callable $iterator optional, the comparision function
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return mixed
     */
    public static function min(iterable $list, callable $iterator = null, $context = null)
    {
        if ($iterator === null && $context === null && is_array($list)) {
            return min(...$list);
        }

        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if ($iterator === null) {
            $iterator = [static::class, 'identity'];
        }

        $min = null;
        $result = null;
        static::each($list, function ($item, $index, $list) use ($iterator, & $min, &$result) {
            if (!isset($min) || ($num = $iterator($item, $index, $list)) < $min) {
                $result = $item;
                $min = $num;
            }
        });

        return $result;
    }

    /**
     * Returns a (stably) sorted copy of list, ranked in ascending order by the results of running each value
     * through iterator. Returns NULL in case of error.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to sort
     * @param callable $iterator the function that generates the criteria by which items are sorted
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function sortBy(iterable $list, callable $iterator, $context = null): array
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        $result = static::map($list, function ($value, $index, $list) use ($iterator) {
            return ['value' => $value, 'criteria' => $iterator($value, $index, $list)];
        });

        return uasort($result, function ($left, $right) {
            return $left['criteria'] <=> $right['criteria'];
        }) ? static::pluck($result, 'value') : null;
    }

    /**
     * Given a list, and an iterator function that returns a key for each element in the list (or a property name),
     * returns an object with an index of each item. Just like groupBy, but for when you know your keys are unique.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to index
     * @param callable,scalar $iterator the function to generate the key or a property name
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @throws InvalidArgumentException if iterator is not callable nor scalar
     *
     * @return array
     */
    public static function indexBy(iterable $list, $iterator, $context = null): array
    {
        if ($context !== null && is_callable($iterator)) {
            $iterator = static::bind($iterator, $context);
        }

        if (!is_callable($iterator) && !is_scalar($iterator)) {
            throw new InvalidArgumentException("iterator should be scalar or callable");
        }

        $result = [];
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            is_scalar($iterator)
                ? $result[static::get($item, $iterator)] = $item
                : $result[$iterator($item, $index, $list)] = $item;
        });

        return $result;
    }

    /**
     * Splits a collection into sets, grouped by the result of running each value through iterator. If iterator is a
     * string instead of a function, groups by the property named by iterator on each of the values.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to group
     * @param callable,scalar $iterator the function to generate the key or a property name
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function groupBy(iterable $list, callable $iterator, $context = null): array
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if (!is_callable($iterator) && !is_scalar($iterator)) {
            throw new InvalidArgumentException("iterator should be scalar or callable");
        }

        $result = [];
        static::each($list, function ($item, $index, $list) use ($iterator, &$result) {
            is_scalar($iterator)
                ? $result[static::get($item, $iterator)][] = $item
                : $result[$iterator($item, $index, $list)][] = $item;
        });

        return $result;
    }

    /**
     * Sorts a list into groups and returns a count for the number of objects in each group. Similar to groupBy, but
     * instead of returning a list of values, returns a count for the number of values in that group.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to group and count
     * @param callable,scalar $iterator the function to generate the key or a property name
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function countBy(iterable $list, callable $iterator, $context = null): array
    {
        return static::map(static::groupBy($list, $iterator, $context), function ($item) {
            return static::size($item);
        });
    }

    /**
     * Returns a shuffled copy of the list.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items to shuffle
     *
     * @return array|null
     */
    public static function shuffle(iterable $list): array
    {
        $list = static::toArray($list);
        return shuffle($list) ? $list : null;
    }

    /**
     * Produce a random sample from the list. Pass a number to return n random elements from the list. Otherwise a
     * single random item will be returned.
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items
     * @param int $n optional, the number of items to pick
     *
     * @return array
     */
    public static function sample(iterable $list, int $n = 1)
    {
        return $n == 1
            ? static::get($list, static::keys($list)[static::random(static::size($list) -1)])
            : array_slice(static::shuffle($list), 0, $n);
    }

    /**
     * Creates a real Array from the list (anything that can be iterated over). This method will also accept scalars
     * such as string, number and even null and will *cast* them into arrays, for instance Underscore::toArray(null)
     * is [] altough Underscore::toArray('a') is ['a'].
     *
     * @category Collection Functions
     *
     * @param iterable $list the list of items
     *
     * @return array
     */
    public static function toArray(iterable $list): array
    {
        if (empty($list)) {
            return [];
        }

        if (func_num_args() > 1) {
            return func_get_args();
        }

        if (is_array($list)) {
            return $list;
        }

        if ($list instanceof ArrayObject || $list instanceof ArrayIterator) {
            return $list->getArrayCopy();
        }

        if ($list instanceof Traversable) {
            return iterator_to_array($list, true);
        }

        return (array)$list;
    }

    /**
     * Return the number of values in the list. This method will also accept scalars such as string, number and even
     * null or resources but will return 1 in that case.
     *
     * @category Collection Functions
     *
     * @param traversable $list the list of items
     *
     * @return int
     */
    public static function size(iterable $list): int
    {
        if (empty($list)) {
            return 0;
        }

        if (is_array($list) || $list instanceof Countable) {
            return count($list);
        }

        if ($list instanceof Traversable) {
            return iterator_count($list);
        }

        return static::reduce($list, function ($memo) {
            return $memo + 1;
        }, 0);
    }

    /**
     * Split a collection into two arrays: one whose elements all satisfy the given predicate, and one whose elements
     * all do not satisfy the predicate.
     *
     * @param  iterable $list the list of items
     * @param  callable $iterator the predicate
     * @param  object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function partition(iterable $list, callable $iterator, $context = null): array
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        $pass = [];
        $fail = [];
        static::each($list, function ($item, $index, $list) use (&$pass, &$fail, $iterator) {
            ${$iterator($item, $index, $list) ? 'pass' : 'fail'}[] = $item;
        });

        return [$pass, $fail];
    }

    /**
     * Array Functions
     * ---------------
     */

    /**
     * @see Underscore::first
     */
    public static function head(iterable $array, int $n = 1)
    {
        return static::first($array, $n);
    }

    /**
     * @see Underscore::first
     */
    public static function take(iterable $array, int $n = 1)
    {
        return static::first($array, $n);
    }

    /**
     * Returns the first element of an array. Passing n will return the first n elements of the array. Passing guard
     * will force the returned value to be an array.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param int $n optional, the number of items to pick
     * @param bool $guard optional, true to always return an array
     *
     * @throws UnexpectedValueException if $n is negative or zero
     *
     * @return mixed,array
     */
    public static function first(iterable $array, int $n = 1, bool $guard = false)
    {
        if (empty($array)) {
            return;
        }

        if ($n <= 0) {
            throw new UnexpectedValueException("invalid number of items $n");
        }

        $result = static::map($array, function ($item) use (&$n) {
            return $n-- ? $item : static::break();
        });

        return $guard || isset($result[1]) ? $result : $result[0];
    }

    /**
     * Returns everything but the last entry of the array. Pass n to exclude the last n elements from the result.
     * Passing guard will force the returned value to be an array.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param int $n optional, the number of items to exclude
     * @param bool $guard optional, true to always return an array
     *
     * @return mixed,array
     */
    public static function initial(iterable $array, int $n = 1, bool $guard = false)
    {
        return static::first($array, static::size($array) - $n, $guard);
    }

    /**
     * Returns the last element of an array. Passing n will return the last n elements of the array. Passing guard
     * will force the returned value to be an array.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param int $n optional, the number of items to pick
     * @param bool $guard optional, true to always return an array
     *
     * @return mixed,array
     */
    public static function last(iterable $array, int $n = 1, bool $guard = false)
    {
        $array = static::toArray($array);
        $result = array_values(array_slice($array, -$n));
        return $guard || isset($result[1]) ? $result : $result[0];
    }

    /**
     * @see Underscore::rest
     */
    public static function tail(iterable $array, int $index = 1, bool $guard = false)
    {
        return static::rest($array, $index, $guard);
    }

    /**
     * @see Underscore::rest
     */
    public static function drop(iterable $array, int $index = 1, bool $guard = false)
    {
        return static::rest($array, $index, $guard);
    }

    /**
     * Returns the rest of the elements in an array. Pass an index to return the values of the array from that index
     * onward. Passing guard will force the returned value to be an array.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param int $index optional, the index from which the items are picked
     * @param bool $guard optional, true to always return an array
     *
     * @return mixed,array
     */
    public static function rest(iterable $array, int $index = 1, bool $guard = false)
    {
        return static::last($array, -$index, $guard);
    }

    /**
     * Returns a copy of the array with all falsy values removed. In PHP, false, null, 0, "", array() and "0" are all
     * falsy.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     *
     * @return array
     */
    public static function compact(iterable $array): array
    {
        return static::filter($array, [static::class, 'identity']);
    }

    /**
     * Flattens a nested array (the nesting can be to any depth). If you pass shallow, the array will only be
     * flattened a single level.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param bool $shallow optional, if true will only flatten on single level
     *
     * @return array
     */
    public static function flatten(iterable $array, bool $shallow = false): array
    {
        if (empty($array)) {
            return [];
        }

        $output = [];
        $flatten = function ($input, $shallow) use (&$output, &$flatten) {
            if ($shallow && static::every($input, 'is_array')) {
                return array_merge(...static::toArray($input));
            }

            static::each($input, function ($item) use ($shallow, &$output, &$flatten) {
                if (static::isTraversable($item)) {
                    $shallow
                        ? ($output = array_merge($output, array_values(static::toArray($item))))
                        : $flatten($item, $shallow);
                } else {
                    $output[] = $item;
                }
            });

            return $output;
        };

        return $flatten($array, $shallow);
    }

    /**
     * Returns a copy of the array with all instances of the values removed.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param array,mixed $values multiple, the value(s) to exclude
     *
     * @return array
     */
    public static function without(iterable $array, ...$values): array
    {
        return static::difference($array, $values);
    }

    /**
     * @see Underscore::uniq
     */
    public static function unique(
        iterable $array,
        bool $isSorted = false,
        callable $iterator = null,
        $context = null
    ): array {
        return static::uniq($array, $isSorted, $iterator, $context);
    }

    /**
     * Produces a duplicate-free version of the array, using === to test object equality. If you know in advance that
     * the array is sorted, passing true for isSorted will run a much faster algorithm. If you want to compute unique
     * items based on a transformation, pass an iterator function.
     *
     * WARNING: this function's cyclomatic complexity is (at least) quadratic ! using it with large arrays
     * (> 1000 items) can be very slow and memory consuming.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param bool $isSorted optional, use a faster algorithm if the list is already sorted
     * @param callable $iterator optional, the comparision function if needed
     * @param object $context optional, if provided will become the context of $iterator
     *
     * @return array
     */
    public static function uniq(
        iterable $array,
        bool $isSorted = false,
        callable $iterator = null,
        $context = null
    ): array {
        if (empty($array)) {
            return [];
        }

        // run a much faster algorithm if a transformation is not needed
        if (is_array($array) && !$iterator) {
            return array_values(array_unique($array));
        }

        $initial = $iterator ? static::map($array, $iterator, $context) : $array;
        $result = [];
        static::each($initial, function ($value, $index) use (&$array, $isSorted, &$result) {
            static $seen = [];

            if ($isSorted) {
                if ($seen[0] !== $value) {
                    $result[] = static::get($array, $index);
                    $seen[0] = $value;
                }
            } else {
                if (!in_array($value, $seen, true)) {
                    $result[] = static::get($array, $index);
                    $seen[] = $value;
                }
            }
        });

        return $result;
    }

    /**
     * Computes the union of the passed-in arrays: the list of unique items, in order, that are present in one or more
     * of the arrays.
     *
     * @category Array Functions
     *
     * @param iterable $arrays the arrays to join
     *
     * @return array
     */
    public static function union(iterable ...$arrays): array
    {
        return static::uniq(static::flatten($arrays, true));
    }

    /**
     * Computes the list of values that are the intersection of all the arrays. Each value in the result is present in
     * each of the arrays.
     *
     * @category Array Functions
     *
     * @param iterable $arrays the arrays to intersect
     *
     * @return array
     */
    public static function intersection(iterable ...$arrays): array
    {
        if (empty($arrays)) {
            return [];
        }

        if (1 == $count = count($arrays)) {
            return $arrays[0];
        }

        if (static::every($arrays, 'is_array')) {
            return array_values(array_intersect(...$arrays));
        }

        $result = [];
        static::each($array, function ($value, $index) use (&$result, $arrays, $count) {
            if (static::contains($result, $value)) {
                return;
            }

            for ($i=1; $i<$count; $i++) {
                if (!static::contains($arrays[$i], $value)) {
                    break;
                }
            }

            if ($i == $count) {
                $result[] = $value;
            }
        });

        return $result;
    }

    /**
     * Similar to without, but returns the values from array that are not present in the other arrays.
     *
     * @category Array Functions
     *
     * @param iterable $arrays the arrays to difference
     *
     * @return array
     */
    public static function difference(iterable ...$arrays): array
    {
        if (empty($arrays)) {
            return [];
        }

        if (count($arrays) == 1) {
            return $arrays[0];
        }

        if (static::every($arrays, 'is_array')) {
            return array_values(array_diff(...$arrays));
        }

        $rest = static::flatten(array_slice($arrays, 1), true);
        return static::values(static::filter($array, function ($value) use ($rest) {
            return !static::contains($rest, $value);
        }));
    }

    /**
     * Merges together the values of each of the arrays with the values at the corresponding position. Useful when you
     * have separate data sources that are coordinated through matching array indexes.
     *
     * @category Array Functions
     *
     * @param iterable $arrays the arrays to zip
     *
     * @return array
     */
    public static function zip(...$arrays): array
    {
        if (empty($arrays)) {
            return [];
        }

        if (1 === $count = count($arrays)) {
            return $arrays[0];
        }

        $result = [];
        for ($i=0; $i<$count; $i++) {
            $result[$i] = static::pluck($arguments, $i);
        }

        return $result;
    }

    /**
     * Converts arrays into objects. Pass either a single list of [key, value] pairs, or a list of keys, and a list of
     * values. If duplicate keys exist, the last value wins.
     *
     * @category Array Functions
     *
     * @param array $list the properties
     * @param array $values optional, the values, if not provided each item of $list is used a pair
     *
     * @return object
     */
    public static function obj(array $list, array $values = null): stdClass
    {
        if (!$list) {
            return (object)[];
        }

        $result = new \stdClass;
        $length = count($list);
        $list = array_values($list);
        for ($i=0; $i<$length; $i++) {
            $values
                ? $result->$list[$i] = $values[$i]
                : $result->$list[$i][0] = $list[$i][1];
        }

        return $result;
    }

    /**
     * Returns the index at which value can be found in the array, or -1 if value is not present in the array. This
     * method uses array_search internally and is not optimized for long array binary search.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param mixed $item the value to look for
     *
     * @return mixed
     */
    public static function indexOf(iterable $array, $item)
    {
        if (is_array($array)) {
            return ($key = array_search($item, $array)) !== false ? $key : -1;
        }

        $found = -1;
        $search = $item;
        static::each($array, function ($item, $key) use ($search, &$found) {
            if ($item == $search) {
                $found = $key;
                throw new BreakException;
            }
        });

        return $found;
    }

    /**
     * Returns the index of the last occurrence of value in the array, or -1 if value is not present. This method uses
     * array_keys internally and is not optimized for long array binary search.
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param mixed $item the value to look for
     *
     * @return mixed
     */
    public static function lastIndexOf(iterable $array, $item)
    {
        if (is_array($array)) {
            return ($keys = array_keys($array, $item, false)) ? array_pop($keys) : -1;
        }

        $found = -1;
        $search = $item;
        static::each($array, function ($item, $key) use ($search, &$found) {
            if ($item == $search) {
                $found = $key;
            }
        });

        return $found;
    }

    /**
     * Uses a binary search to determine the index at which the value should be inserted into the list in order
     * to maintain the list's sorted order. If an iterator is passed, it will be used to compute the sort ranking of
     * each value, including the value you pass. Iterator may also be the string name of the property to sort by (eg.
     * length).
     *
     * @category Array Functions
     *
     * @param iterable $array the list of items
     * @param mixed $value the value to find the index for
     * @param callable,scalar $iterator optional, the function by which a value is evaluated or a property's name
     * @param object $context optional, if provided will become the context for $iterator
     *
     * @return int
     */
    public static function sortedIndex(iterable $array, $value, $iterator = null, $context = null): int
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        if (!is_array($array) && !$array instanceof \ArrayAccess) {
            $array = static::toArray($array);
        }

        $iterator = $iterator === null
            ? [static::class, 'identity']
            : static::getLookupIterator($iterator);

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
     * by step, exclusive. This method uses range internally.
     *
     * @category Array Functions
     *
     * @param int $start the starting index
     * @param int $stop optional, the ending index
     * @param int $step optional, the iteration step
     *
     * @return array
     */
    public static function range(int $start, int $stop = null, int $step = 1): array
    {
        if (func_num_args() <= 1) {
            $stop  = $start ?: 0;
            $start = 0;
        }

        return range($start, $stop, $step);
    }

    /**
     * Same as range but yields the values instead of creating an array.
     *
     * @since 0.3.0
     * @category Array Functions
     *
     * @param  int $start the starting index
     * @param  int $stop optional, the ending index
     * @param  int $step optional, the iteration step
     *
     * @return Generator
     */
    public static function xrange(int $start, int $stop = null, int $step = 1): Generator
    {
        if (func_num_args() <= 1) {
            $stop  = $start ?: 0;
            $start = 0;
        }

        for ($i=$start; $i<=$stop; $i+=$step) {
            yield $i;
        }
    }

    /**
     * Function (uh, ahem) Functions
     * -----------------------------
     */

    /**
     * Wrap the first function inside of the wrapper function, passing it as the first argument. This allow the
     * wrapper to execute code before and after the function runs, adjust the arguments and execute it conditionnaly.
     * Arguments are passed along to the wrapper function.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     *
     * @return closure
     */
    public static function wrap(callable $function, callable $wrapper): Closure
    {
        return function (...$args) use ($function, $wrapper) {
            array_unshift($args, $function);
            return $wrapper(...$args);
        };
    }

    /**
     * Returns a new negated version of the predicate function.
     *
     * @since 0.2.0
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     *
     * @return closure
     */
    public static function negate(callable $function): Closure
    {
        return function (...$args) use ($function) {
            return !$function(...$args);
        };
    }

    /**
     * Returns the composition of a list of functions, where each function consumes the return value of the function
     * that follows. In math terms, composing the functions f(), g(), and h() produces f(g(h())).
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $functions the functions to compose
     *
     * @return closure
     */
    public static function compose(callable ...$functions): Closure
    {
        return function (...$args) use ($functions) {
            foreach ($functions as $function) {
                $args = $function(...(array)$args);
            }

            return $args;
        };
    }

    /**
     * Creates a version of the function that will only be run after first being called $count times. Please note that
     * the function shall not recieve parameters.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param int $count the number of times the $function shall be executed
     * @param callable $function the function
     *
     * @return closure
     */
    public static function after(int $count, callable $function): Closure
    {
        if ($count <= 0) {
            throw new InvalidArgumentException("invalid count $count");
        }

        return function (...$args) use (&$count, $function) {
            if (--$count > 0) {
                return;
            }

            return $function(...$args);
        };
    }

    /**
     * Creates a version of the function that can be called no more than count times. The result of the last function
     * call is memoized and returned when count has been reached.
     *
     * @since 0.2.0
     * @category Function (uh, ahem) Functions
     *
     * @param  int $count the number of times the $function shall be executed
     * @param  callable $function the function
     *
     * @return closure
     */
    public static function before(int $count, callable $function): Closure
    {
        return function (...$args) use (&$count, $function) {
            static $memo;

            if (--$count > 0) {
                $memo = $function(...$args);
            }

            return $memo;
        };
    }

    /**
     * Creates a version of the function that can only be called one time. Repeated calls to the modified function
     * will have no effect, returning the value from the original call. Useful for initialization functions, instead
     * of having to set a boolean flag and then check it later.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     *
     * @return closure
     */
    public static function once(callable $function): Closure
    {
        return static::before(2, $function);
    }

    /**
     * Partially apply a function by filling in any number of its arguments. Not all the arguments have to be present
     * on the partial construction.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     * @param mixed $arguments the arguments
     *
     * @return closure
     */
    public static function partial(callable $function, ...$arguments): Closure
    {
        return function ($args) use ($function, $arguments) {
            return $function(...array_merge($args, $arguments));
        };
    }

    /**
     * Bind a function to an object, meaning that whenever the function is called, the value of $this will be the
     * object. Optionally, pass arguments to the function to pre-fill them, also known as partial application.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     * @param object,array $object the object to bind the closure to
     * @param mixed $arguments the arguments
     *
     * @throws InvalidArgumentException if $object is not an object
     * @throws RuntimeException if the closure cannot be bound to $object (static closures)
     *
     * @return closure
     */
    public static function bind(callable $function, $object, ...$arguments): Closure
    {
        if (!$function instanceof Closure) {
            $function = Closure::fromCallable($function);
        }

        if (is_array($object)) {
            $object = (object)$object;
        }

        if (!is_object($object)) {
            throw new InvalidArgumentException("not an object");
        }

        if (false === $function = Closure::bind($function, $object)) {
            throw new RuntimeException("could not bind");
        }

        return !empty($arguments)
            ? static::partial($function, ...$arguments)
            : $function;
    }

    /**
     * Bind a function to a class, meaning that whenever the function is called, the value of self or static will be
     * the class. Optionally, pass arguments to the function to pre-fill them, also known as partial application.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param closure $function the function
     * @param object,string $class the object or classname to bind the closure to
     *
     * @throws InvalidArgumentException if $class does not exists
     * @throws RuntimeException if the closure cannot be bound to $class
     *
     * @return closure
     */
    public static function bindClass(callable $function, $class, ...$arguments): Closure
    {
        if (is_string($class) && !class_exists($class)) {
            throw new InvalidArgumentException("no such class $class");
        }

        if (!$function instanceof Closure) {
            $function = Closure::fromCallable($function);
        }

        // assigning a class context for a closure is never an issue
        $function = Closure::bind($function, null, $class);

        return !empty($arguments)
            ? static::partial($function, ...$arguments)
            : $function;
    }

    /**
     * Binds a number of methods on the object, specified by methodNames, to be run in the context of that object
     * whenever they are invoked. Very handy for binding functions that are going to be used as event handlers, which
     * would otherwise be invoked with a fairly useless this. methodNames are required. Keep in mind PHP doesn't
     * allow to call a closure property value like a method, for instance $o->myClosure(), given $o is an instance of
     * stdClass, won't work.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param object $object the object
     * @param callable $methodNames the functions to attach
     *
     * @return object
     */
    public static function bindAll($object, callable ...$methodNames)
    {
        foreach ($methodNames as $methodName) {
            if (isset($object->$methodName)) {
                $object->$methodName = static::bind($object->$methodName, $object);
            }
        }

        return $object;
    }

    /**
     * Memoizes a given function by caching the computed result. Useful for speeding up slow-running computations. If
     * passed an optional hashFunction, it will be used to compute the hash key for storing the result, based on the
     * arguments to the original function. The default hashFunction just uses the first argument to the memoized
     * function as the key.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function to memoize
     * @param callable $hashFunction optional, if provided will be used to hash $function's results
     * @param array,ArrayAccess $cache optional, function's results cache
     *
     * @return closure
     */
    public static function memoize(callable $function, callable $hashFunction = null, &$cache = null): Closure
    {
        $hashFunction = $hashFunction ?: function ($arguments) {
            return array_shift($arguments);
        };

        if ($cache && !(is_array($cache) || $cache instanceof ArrayAccess)) {
            throw new InvalidArgumentException("invalid cache type");
        }

        $cache = $cache ?: [];
        return function (...$args) use ($function, $hashFunction, &$cache) {
            $ckey = $hashFunction($args);

            return isset($cache[$ckey]) || array_key_exists($ckey, $cache)
                ? $cache[$ckey]
                : $cache[$ckey] = $function(...$args);
        };
    }

    /**
     * Creates and returns a new, throttled version of the passed function, that, when invoked repeatedly, will only
     * actually call the original function at most once per every wait milliseconds. Useful for rate-limiting events
     * that occur faster than you can keep up with.
     *
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     * @param int $wait the time to wait between each call (in milliseconds)
     *
     * @return closure
     */
    public static function throttle(callable $function, int $wait): Closure
    {
        return function () use ($function, $wait) {
            static $pretime;

            $curtime = microtime(true);
            if (!$pretime || ($curtime - $pretime) >= ($wait / 1000)) {
                $pretime = $curtime;
                $function();
            }
        };
    }

    /**
     * Call (execute) the given function, optionnaly bound to $context, with the given arguments and return its result.
     *
     * @since 0.2.0
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     * @param object $context the function's context
     * @param mixed $args the function arguments
     *
     * @return mixed
     */
    public static function call(callable $function, $context = null, ...$arguments)
    {
        return static::apply($function, $context, ...$args);
    }

    /**
     * Call (execute) the given function, optionnaly bound to $context, with the given argument list and return its
     * result.
     *
     * @since 0.2.0
     * @category Function (uh, ahem) Functions
     *
     * @param callable $function the function
     * @param object $context the function's context
     * @param array $arguments the arguments
     *
     * @return mixed
     */
    public static function apply(callable $function, $context = null, array $arguments = [])
    {
        if ($context !== null) {
            $function = static::bind($function, $context);
        }

        return $function(...$arguments);
    }

    /**
     * Object Functions
     * ----------------
     */

    /**
     * Retrieve all the names of the object's properties.
     *
     * @category Object Functions
     *
     * @param iterable $object the list from which the keys are extracted
     *
     * @return array
     */
    public static function keys(iterable $object): array
    {
        if ($object instanceof stdClass) {
            $object = (array)$object;
        }

        if (is_array($object)) {
            return array_keys($object);
        }

        $result = [];
        static::each($object, function ($item, $key) use (&$result) {
            $result[] = $key;
        });

        return $result;
    }

    /**
     * Return all of the values of the object's properties.
     *
     * @category Object Functions
     *
     * @param iterable $object the list from which the values are extracted
     *
     * @return array
     */
    public static function values(iterable $object): array
    {
        if ($object instanceof stdClass) {
            $object = (array)$object;
        }

        if (is_array($object)) {
            return array_values($object);
        }

        $result = [];
        static::each($object, function ($item) use (&$result) {
            $result[] = $item;
        });

        return $result;
    }

    /**
     * Convert an object into a list of [key, value] pairs.
     *
     * @category Object Functions
     *
     * @param traversable $object the list of items to convert to pairs
     *
     * @return array
     */
    public static function pairs(iterable $object): array
    {
        $result = [];
        foreach ($object as $key => $value) {
            $result[] = [$key, $value];
        }

        return $result;
    }

    /**
     * Returns a copy of the object where the keys have become the values and the values the keys. For this to work,
     * all of your object's values should be unique and string serializable.
     *
     * @category Object Functions
     *
     * @param traversable $object the object to invert
     *
     * @return iterable
     */
    public static function invert(iterable $object): iterable
    {
        if (!is_array($object)) {
            $castDown = (boolean)$object = static::toArray($object);
        }

        return !empty($castDown) ? (object)array_flip($object) : array_flip($object);
    }

    /**
     * @see Underscore::functions
     */
    public static function methods(iterable $object): array
    {
        return static::functions($object);
    }

    /**
     * Returns a sorted list of the names of every method in an object — that is to say, the name of every function
     * property of the object.
     *
     * @category Object Functions
     *
     * @param traversable,object $object the object to extract the functions from
     *
     * @return array
     */
    public static function functions(iterable $object): array
    {
        return (is_array($object) || $object instanceof stdClass)
            ? static::keys(static::filter($object, [static::class, 'isFunction']))
            : get_class_methods($object);
    }

    /**
     * Copy all of the properties in the source objects over to the destination object, and return the destination
     * object. It's in-order, so the last source will override properties of the same name in previous arguments.
     *
     * @category Object Functions
     *
     * @param iterable $destination the destination object
     * @param iterable $sources multiple, the source objects
     *
     * @return iterable
     */
    public static function extend(iterable $destination, iterable ...$sources): iterable
    {
        static::each($sources, function ($source) use (&$destination) {
            static::each($source, function ($value, $name) use (&$destination) {
                static::set($destination, $name, $value);
            });
        });

        return $destination;
    }

    /**
     * Returns a copy of the object, filtered to only have values for the whitelisted keys (or array of valid keys). If
     * provided object is an object (in the broadest sense), a stdClass instance is returned, otherwise an array is
     * returned.
     *
     * @category Object Functions
     *
     * @param iterable $object the object to pick properties on
     * @param scalar ...$keys the keys to pick
     *
     * @return iterable
     */
    public static function pick($object, ...$keys): iterable
    {
        $result = is_object($object) ? new stdClass : [];
        static::each($keys, function ($key) use ($object, &$result) {
            if (static::has($object, $key)) {
                static::set($result, $key, static::get($object, $key));
            }
        });

        return $result;
    }

    /**
     * Return a copy of the object, filtered to omit the blacklisted keys (or array of keys). If provided object is an
     * object (in the broadest sense), a stdClass instance is returned, otherwise an array is returned.
     *
     * @category Object Functions
     *
     * @param iterable $object the object to exclude keys from
     * @param scalar ...$keys the keys to omit
     *
     * @return iterable
     */
    public static function omit(iterable $object, ...$keys): iterable
    {
        $result = is_object($object) ? new stdClass : [];
        $keys = array_flip($keys);
        static::each($object, function ($value, $name) use ($keys, &$result) {
            if (!isset($keys[$name])) {
                static::set($result, $name, $value);
            }
        });

        return $result;
    }

    /**
     * Fill in null properties in object with values from the defaults objects, and return the object. As soon as the
     * property is filled, further defaults will have no effect.
     *
     * @category Object Functions
     *
     * @param iterable $object the object to fill
     * @param iterable $defaults multiple, the objects or array that will fill object's missing keys
     *
     * @return object
     */
    public static function defaults(iterable $object, iterable ...$defaults): iterable
    {
        $result = is_object($object) ? clone $object : $object;
        static::each($defaults, function ($default) use (&$result, $object) {
            if (is_array($object) && is_array($default)) {
                return $result += $default;
            }

            static::each($default, function ($item, $index) use (&$result) {
                if (!static::has($result, $index)) {
                    static::set($result, $index, $item);
                }
            });
        });

        return $object;
    }

    /**
     * @see Underscore::duplicate
     */
    public static function copy($object)
    {
        return static::duplicate($object);
    }

    /**
     * Create a shallow-copied clone of the object. Any nested objects or arrays will be copied by reference, not
     * duplicated. This method is safe to use with arrays.
     *
     * @category Object Functions
     *
     * @param mixed $object the object to clone
     *
     * @return mixed
     */
    public static function duplicate($object)
    {
        if (!is_array($object) && !is_object($object)) {
            return $object;
        }

        return is_object($object)
            ? clone $object
            : static::extend([], $object);
    }

    /**
     * Invokes interceptor with the object, and then returns object. The primary purpose of this method is to "tap
     * into" a method chain, in order to perform operations on intermediate results within the chain.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param callable $interceptor the function to call with the object as parameter
     *
     * @return mixed
     */
    public static function tap($object, callable $interceptor)
    {
        $interceptor($object);
        return $object;
    }

    /**
     * Tells whether the object has a given property. The property must be publicly accessible.
     *
     * @category Object Functions
     *
     * @param object,array $object the object
     * @param mixed $key the key
     *
     * @return bool
     */
    public static function has($object, $key)
    {
        if (is_array($object) && (isset($object[$key]) || array_key_exists($key, $object))) {
            return true;
        }

        if (is_object($object) && (isset($object->$key) || property_exists($object, $key))) {
            return true;
        }

        return false;
    }

    /**
     * Returns a function that will itself return the key property of any passed-in object.
     *
     * @since 0.2.0
     * @category Object Functions
     *
     * @param mixed $key the key or offset to get
     *
     * @return closure
     */
    public static function property($key): Closure
    {
        return function ($object) use ($key) {
            return static::get($object, $key);
        };
    }

    /**
     * Returns a predicate function that will tell you if a passed in object contains all of the key/value properties
     * present in properties.
     *
     * @since 0.2.0
     * @category Object Functions
     *
     * @param iterable $properties the properties used by predicate
     *
     * @return closure
     */
    public static function matches(iterable $properties): Closure
    {
        $length = static::size($properties);
        return function ($object) use ($properties, $length) {
            if ($object === null) {
                return !$length;
            }

            return static::every($properties, function ($value, $key) {
                return static::get($object, $key) == $value;
            });
        };
    }

    /**
     * Get the object's key value. If such keys doesn't exists, the default value is returned.
     *
     * @category Object Functions
     *
     * @param object,array $object the object
     * @param scalar $key the key
     * @param mixed $default optional, the default value to return in case the key doesn't exists
     *
     * @return mixed
     */
    public static function get($object, $key, $default = null)
    {
        if (is_array($object)) {
            return $object[$key] ?? $default;
        }

        if (is_object($object)) {
            return $object->$key ?? $default;
        }

        return $default;
    }

    /**
     * Set object's key value.
     *
     * @category Object Functions
     *
     * @param mixed &$object the object
     * @param scalar $key the key
     * @param mixed $value the value to set
     *
     * @return mixed
     */
    public static function set(&$object, $key, $value)
    {
        if (is_array($object)) {
            $object[$key] = $value;
        }

        if (is_object($object)) {
            $object->$key = $value;
        }
    }

    /**
     * Tells whether the object is of the given type, or class, or pseudo-type. You may pass several types at once
     * (using an array of types or by passing several types as arguments), Underscore::is will return true if object
     * matchs any of these.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param string ...$types the types to test
     *
     * @return bool
     */
    public static function is($object, string ...$types): bool
    {
        return static::some(array_map('strtolower', $types), function ($name) use ($object) {
            return static::typeOf($object, false) == $type || $object instanceof $type;
        });
    }

    /**
     * Performs an optimized deep comparison between the two objects, to determine if they should be considered equal.
     *
     * @category Object Functions
     *
     * @param mixed $object the first object
     * @param mixed $other the second object
     *
     * @return bool
     */
    public static function isEqual($object, $other): bool
    {
        return static::equal($object, $other, [], []);
    }

    /**
     * Returns true if object contains no values (no enumerable own-properties). Works with scalars as well.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isEmpty($object): bool
    {
        if (empty($object)) {
            return true;
        }

        return static::size($object) == 0;
    }

    /**
     * Returns true if object is an array or usable like an array. If the optionnal native parameter is set to true, it
     * will only return true if object is a native array.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param bool $native optional, if true will no consider instances of ArrayAccess as arrays
     *
     * @return bool
     */
    public static function isArray($object, bool $native = false): bool
    {
        return $native
            ? is_array($object)
            : is_array($object) || $object instanceof ArrayAccess;
    }

    /**
     * Returns true if value is an Object.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isObject($object): bool
    {
        return is_object($object);
    }

    /**
     * Returns true if object is a Function.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isFunction($object): bool
    {
        return is_callable($object);
    }

    /**
     * @see Underscore::isNumber
     */
    public static function isNum($object, bool $native = false): bool
    {
        return static::isNumber($object, $native);
    }

    /**
     * @see Underscore::isNumber
     */
    public static function isNumeric($object, bool $native = false): bool
    {
        return static::isNumber($object, $native);
    }

    /**
     * Returns true of object is a Number. If the optionnal native parameter is set to true, it will only return true
     * if object is a native int or float.
     *
     * @category Object Functions
     *
     * @param mixed $object the object to pick properties on
     * @param bool $native optional, if true will not consider SplType instances as numbers
     *
     * @return bool
     */
    public static function isNumber($object, bool $native = false): bool
    {
        return static::isInteger($object, $native)
            || static::isFloat($object, $native);
    }

    /**
     * @see Underscore::isInteger
     */
    public static function isInt($object, bool $native = false): bool
    {
        return static::isInteger($object, $native);
    }

    /**
     * Returns true if the object is an integer. If the optional native parameter is set to true, it will only return
     * true if object is a native int.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param bool $native optional, if true will not consider instances of SplInt as integers
     *
     * @return bool
     */
    public static function isInteger($object, bool $native = false): bool
    {
        return $native
            ? is_int($object)
            : is_int($object) || $object instanceof SplInt;
    }

    /**
     * Returns true if the object is a float. If the optional native parameter is set to true, it will only return true
     * if object is a native float.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param bool $native optional, if true will not consider instances of SplFloat as integers
     *
     * @return bool
     */
    public static function isFloat($object, bool $native = false): bool
    {
        if (is_nan($object)) {
            return false;
        }

        if (is_float($object)) {
            return true;
        }

        if (!$native) {
            return false;
        }

        if ($object instanceof SplFloat) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if object is a String. If object is an object with a __toString method, it will be considered
     * as a string as well. If the optionnal native parameter is set to true, it will only return true if object is a
     * native string.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param bool $native optional, if true will not consider object with toString or SplString as strings
     *
     * @return bool
     */
    public static function isString($object, bool $native = false): bool
    {
        if (is_string($object)) {
            return true;
        }

        if (!$native) {
            return false;
        }

        if ($object instanceof SplString) {
            return true;
        }

        if (method_exists($object, '__toString')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if object is a DateTime instance. Everything the strtotime function can understand is also
     * considered a date.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isDate($object): bool
    {
        return $object instanceof DateTime
            || (static::isString($object) && strtotime((string)$object) !== false);
    }

    /**
     * Returns true if object is a valid regular expression (PCRE).
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isRegExp($object): bool
    {
        return static::isString($object)
            && (@preg_match((string)$object, null) !== false);
    }

    /**
     * Returns true if object is a finite number.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isFinite($object): bool
    {
        return is_finite($object);
    }

    /**
     * Returns true if object is NaN (Not a Number).
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isNaN($object): bool
    {
        return is_double($object) && is_nan($object);
    }

    /**
     * @see Underscore::isBoolean
     */
    public static function isBool($object, bool $native = false): bool
    {
        return static::isBoolean();
    }

    /**
     * Returns true if object is a Boolean. If the optionnal native parameter is set to true, it will only return true
     * if object is a native boolean.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param bool $native optional, if true will not consider instances of SplBool as integers
     *
     * @return bool
     */
    public static function isBoolean($object, bool $native = false): bool
    {
        if (is_bool($object)) {
            return true;
        }

        if (!$native) {
            return false;
        }

        if ($object instanceof SplBool) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if object is Null.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isNull($object): bool
    {
        return is_null($object);
    }

    /**
     * Returns true if $object is a scalar. If the optionnal native parameter is set to true, it will only return true
     * if object is a native scalar.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isScalar($object, bool $native = false): bool
    {
        if (is_scalar($object)) {
            return true;
        }

        if (!$native) {
            return false;
        }

        if ($object instanceof SplType) {
            return true;
        }

        return false;
    }

    /**
     * @see Underscore::isIterable
     */
    public static function isTraversable($object): bool
    {
        return static::isIterable($object);
    }

    /**
     * Returns true if the object can be traversed with a foreach loop.
     *
     * @since 0.3.0
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isIterable($object): bool
    {
        return static::is($object, ['Traversable', 'stdClass', 'array']);
    }

    /**
     * Returns true if the object is a resource (like a file handle returned by fopen).
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     *
     * @return bool
     */
    public static function isResource($object): bool
    {
        return is_resource($object);
    }

    /**
     * @see Underscore::typeOf
     */
    public static function getType($object, bool $class = true): string
    {
        return static::typeOf($object, $class);
    }

    /**
     * Gets the class of given object or its native type. This function aggregates most of the is* functions and can
     * be seen as a more preceise version of PHP native function gettype. The class parameters lets you know the
     * exact type of the object, if set to false 'object' is returned for objects. Otherwise will return one of the
     * Underscore::TYPE_* constants.
     *
     * @category Object Functions
     *
     * @param mixed $object the object
     * @param bool $class optional, if true will return the exact class of $object instead of TYPE_OBJECT
     *
     * @return string
     */
    public static function typeOf($object, bool $class = true): string
    {
        switch (true) {
            case static::isNull($object):
                return 'null';

            case static::isBoolean($object):
                return 'boolean';

            case static::isInteger($object):
                return 'integer';

            case static::isNaN($object):
                return 'nan';

            case static::isFloat($object):
                return 'float';

            case static::isString($object):
                return 'string';

            case static::isArray($object):
                return 'array';

            case static::isResource($object):
                return 'resource';

            case static::isObject($object):
                return $class ? get_class($object) : 'object';

            default:
                return 'unknown';
        }
    }

    /**
     * Utility Functions
     * -----------------
     */

    /**
     * Returns the same value that is used as the argument. In math: f(x) = x. This function looks useless, but is used
     * throughout Underscore as a default iterator.
     *
     * @category Utility Functions
     *
     * @param mixed $value the value to return
     *
     * @return mixed
     */
    public static function identity($value)
    {
        return $value;
    }

    /**
     * Creates a function that returns the same value that is used as the argument of _::constant.
     *
     * @since 0.2.0
     * @category Utility Functions
     *
     * @param mixed $value the value
     *
     * @return Closure
     */
    public static function constant($value): Closure
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Returns undefined irrespective of the arguments passed to it. Useful as the default for optional callback
     * arguments.
     *
     * @since 0.2.0
     * @category Utility Functions
     *
     * @return void
     */
    public static function noop()
    {
        return;
    }

    /**
     * Invokes the given iterator function n times. Each invocation of iterator is called with an index argument.
     * Produces an array of the returned values.
     *
     * @category Utility Functions
     *
     * @param int $n the number of time $iterator will be run
     * @param callable $iterator the iterator function
     * @param object $context optional, if provided will become the context for $iterator
     *
     * @return array
     */
    public static function times(int $n, callable $iterator, $context = null): array
    {
        if ($context !== null) {
            $iterator = static::bind($iterator, $context);
        }

        $n = max(0, $n);
        $accum = [];
        for ($i=0; $i<$n; $i++) {
            $accum[] = $iterator($i);
        }

        return $accum;
    }

    /**
     * Returns a random integer between min and max, inclusive. If you only pass one argument, it will return a number
     * between 0 and that number.
     *
     * @category Utility Functions
     *
     * @param int $min the lower bound (or the max if $max is null, the min being 0 then)
     * @param int $max optional the upper bound
     *
     * @return int
     */
    public static function random(int $min, ?int $max): int
    {
        if ($max === null) {
            [$min, $max] = [0, $min];
        }

        return random_int($min, $max);
    }

    /**
     * Allows you to extend Underscore with your own utility functions. Pass a hash of array('name' => function)
     * definitions to have your functions added to the Underscore library, as well as the OOP wrapper.
     *
     * @deprecated 0.3.0
     * @category Utility Functions
     *
     * @param array $functions an collection of functions to add to the Underscore class
     *
     * @return array
     */
    public static function mixin(array $functions): array
    {
        foreach ($functions as $key => $value) {
            Container::set("underscore.method.{$key}", $value);
        }

        return $functions;
    }

    /**
     * Returns callable version of any Underscore method (event the user defined ones).
     *
     * @deprecated 0.3.0
     * @category Utility Functions
     *
     * @param string $method multiple, the Underscore's method name(s)
     *
     * @return callable,array
     */
    public static function provide(...$methods)
    {
        return count($methods)
            ? [static::class, $method]
            : static::map($methods, function ($method) use ($class) {
                return [static::class, $method];
            });
    }

    /**
     * Generate a pseudo-unique id.
     *
     * @category Utility Functions
     *
     * @param string $prefix optional, a prefix for the id
     *
     * @return string
     */
    public static function uniqueId(string $prefix = ""): string
    {
        return uniqid($prefix);
    }

    /**
     * Escapes a string for insertion into HTML, replacing &, <, >, ", ', and / characters.
     *
     * @category Utility Functions
     *
     * @param string $string the string to escape
     *
     * @return string
     */
    public static function escape(string $string): string
    {
        return htmlentities((string)$string);
    }

    /**
     * The opposite of escape, replaces &amp;, &lt;, &gt;, &quot;, &#x27;, and &#x2F; with their unescaped
     * counterparts.
     *
     * @category Utility Functions
     *
     * @param string $string the string to unescape
     *
     * @return string
     */
    public static function unescape(string $string): string
    {
        return html_entity_decode((string)$string);
    }

    /**
     * If the value of the named property is a function then invoke it with the object as context; otherwise, return
     * it.
     *
     * @category Utility Functions
     *
     * @param object,array $object the object or array
     * @param scalar $property the property to get
     *
     * @return mixed
     */
    public static function result($object, $property)
    {
        $value = static::get($object, $property);

        return is_callable($value)
            ? static::call($value, $object)
            : $value;
    }

    /**
     * The equivalent of the finally keywork (available since PHP 5.5).
     *
     * @deprecated 0.3.0
     * @since 0.2.0
     * @category Utility Functions
     *
     * @param callable $function a function
     * @param callable $finally  another function that will *always* be executed after $function
     * @param object $context the functions context
     *
     * @return mixed
     */
    public static function lastly(callable $function, callable $finally, $context = null)
    {
        if ($context !== null) {
            $function = static::bind($function, $context);
            $finally = static::bind($function, $context);
        }

        try {
            $result = $function();
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            $finally();
        }

        return $result;
    }

    /**
     * Returns an integer timestamp for the current time.
     *
     * @return int
     */
    public static function now(): int
    {
        return time();
    }

    /**
     * By default, Underscore uses ERB-style template delimiters, change the following template settings to
     * use alternative delimiters.
     *
     * @var array
     */
    protected const TEMPLATE_SETTINGS = [
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
    private const PATTERN_NO_MATCH = "/(.)^/";

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
     * @category Utility Functions
     *
     * @param string $templateString the template buffer
     * @param iterable $data if provided will compute the template using this array as variables
     * @param iterabel $settings il provided will override default matchers (use with care)
     *
     * @return closure,string
     */
    public static function template(string $templateString, iterable $data = [], iterable $settings = [])
    {
        $settings = static::defaults([], $settings, static::TEMPLATE_SETTINGS, [
            'evaluate'    => static::PATTERN_NO_MATCH,
            'interpolate' => static::PATTERN_NO_MATCH,
            'escape'      => static::PATTERN_NO_MATCH,
        ]);

        $pattern = '~' . implode('|', static::map(
            static::pick($settings, 'escape', 'interpolate', 'evaluate'),
            function ($subpattern, $name) {
                return "(?<$name>" . trim($subpattern, $subpattern[0]) . ")";
            }
        )) . '|$~';

        $templateString = preg_replace_callback($pattern, function ($match) {
            if (!empty($match['escape'])) {
                return sprintf('<?php echo %s::escape(%s) ?>', static::class, trim($match[2]));
            }

            if (!empty($match['interpolate'])) {
                return sprintf('<?php echo %s ?>', trim($match[4]));
            }

            if (!empty($match['evaluate'])) {
                return sprintf('<?php %s ?>', trim($match[6]));
            }
        }, $templateString);

        $templateFunction = create_function(
            '$data',
            'try { extract($data); ob_start(); ?>' . $templateString . '<?php }'.
            'catch (\\Exception $e) { throw $e; }' .
            'finally { return ob_get_clean(); }'
        );

        return $data ? $templateFunction(static::toArray($data)) : $templateFunction;
    }

    /**
     * Chaining
     * --------
     */

    /**
     * Returns a wrapped object. Calling methods on this object will continue to return wrapped objects until value
     * is used. Calling chain will cause all future method calls to return wrapped objects. When you've finished the
     * computation, use value to retrieve the final value.
     *
     * @category Chaining
     *
     * @param mixed $object the chaining initial state
     *
     * @return Proxy
     */
    public static function chain($object): Proxy
    {
        return new Proxy($object, new static);
    }

    /**
     * Class Forgery
     * -------------
     */

    /**
     * @see Underscore::forge
     */
    public static function strategy(string $classname): bool
    {
        return static::forge($classname);
    }

    /**
     * Create new mixins on runtime. The implementation is based on Bob Weinand's idea of Scala traits implementation
     * in PHP (see it here https://gist.github.com/bwoebi/7319798). This method decomposes the $classname to create
     * a new class, using '\with' as a separator for traits.
     *
     * @since 0.2.0
     * @category Class Forgery
     *
     * @param string $classname the class to forge
     *
     * @throws RuntimeException if class or traits are not availabled
     *
     * @return boolean
     */
    public static function forge(string $classname): bool
    {
        if (class_exists($classname)) {
            return true;
        }

        if (strpos($classname, '\\with') === false) {
            return false;
        }

        list($namespace, $class) = str_split($classname, strrpos($classname, '\\'));
        $class = substr($class, 1);

        $traits = explode('\\with', $classname);
        $base = '\\' . trim(array_shift($traits), '\\');

        if (!class_exists($base, true)) {
            throw new RuntimeException("class $base does not exists");
        }

        if (empty($traits)) {
            return false;
        }

        $use = '';
        foreach ($traits as $trait) {
            if (!trait_exists($trait, true)) {
                throw new RuntimeException("trait $trait does not exists");
            }

            $use .= "use $trait; ";
        }

        // eval is now officially your friend!
        return @eval("namespace $namespace { class $class extends $base { $use } }") !== false;
    }

    /**
     * Internal Functions
     * ------------------
     */

    /**
     * Allows you to call the user defined functions registered with Underscore::mixin
     *
     * @category Internal Functions
     *
     * @param string $method the method to call
     * @param array $arguments the method's arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        if (!Container::has($key = "underscore.method.{$method}")) {
            throw new BadMethodCallException("no such method $method");
        }

        return Container::get($key)(...$arguments);
    }

    /**
     * Allows object calls toward user defined static methods
     *
     * @category Internal Functions
     *
     * @param string $method the method to call
     * @param array $arguments the method's arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return static::__callStatic($method, $arguments);
    }

    /**
     * Returns an iterator that matches a list item against properties.
     *
     * @category Internal Functions
     *
     * @param iterable $properties the properties to filter on
     *
     * @return closure
     */
    protected static function getListfilter(iterable $properties): Closure
    {
        return function ($item) use ($properties) {
            foreach ($properties as $property => $value) {
                if (static::get($item, $property) != $value) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * An internal function to generate lookup iterators.
     *
     * @category Internal Functions
     *
     * @param mixed $value the value to iterate with
     *
     * @return mixed,closure
     */
    protected static function getLookupIterator($value)
    {
        return is_callable($value) ? $value : function ($object) use ($value) {
            return static::get($object, $value);
        };
    }

    /**
     * Tells if two objects are equals.
     *
     * @category Internal Functions
     *
     * @param mixed $a the first value
     * @param mixed $b the second $value
     * @param array $aStack the first recursion stack
     * @param array $bStack the second recursion stack
     *
     * @return bool
     */
    protected static function equal($a, $b, array $aStack, array $bStack): bool
    {
        if ($a === null && $b === null) {
            return true;
        }

        // Two resources are always considered different since there is no way to compare them
        if (is_resource($a) && is_resource($b)) {
            return false;
        }

        // Perform classic scalar comparison.
        if (is_scalar($a) && is_scalar($b)) {
            return $a == $b;
        }

        // Assume equality for cyclic structures. The algorithm for detecting cyclic structures is adapted from
        // ES 5.1 section 15.12.3, abstract operation JO.
        $length = count($aStack);
        while ($length--) {
            if ($aStack[$length] == $a) {
                return $bStack[$length] == $b;
            }
        }

        // Add the first object to the stack of traversed objects.
        $aStack[] = $a;
        $bStack[] = $b;

        $result = true;
        $size = 0;
        if (static::isTraversable($a) && static::isTraversable($b)) {
            // Try to deep compare sequences.
            static::each($a, function ($item, $key) use (&$a, &$b, &$aStack, &$bStack, &$size, &$result) {
                $size++;
                $result = static::has($b, $key) && static::equal(
                    static::get($a, $key),
                    static::get($b, $key),
                    $aStack,
                    $bStack
                );

                if (!$result) {
                    throw new BreakException;
                }
            });

            if ($size != static::size($b)) {
                $result = false;
            }
        } else {
            // Last hope comparison.
            $result = $a == $b;
        }

        // Remove the first object from the stack of traversed objects.
        array_pop($aStack);
        array_pop($aStack);

        return $result;
    }
}
