<?php
//    __  __          __                                            __
//   / / / /___  ____/ /__  ___________________  ________    ____  / /_  ____
//  / / / / __ \/ __  / _ \/ ___/ ___/ ___/ __ \/ ___/ _ \  / __ \/ __ \/ __ \
// / /_/ / / / / /_/ /  __/ /  (__  ) /__/ /_/ / /  /  __/ / /_/ / / / / /_/ /
// \____/_/ /_/\__,_/\___/_/  /____/\___/\____/_/   \___(_) .___/_/ /_/ .___/
//                                                       /_/         /_/
//
// Build Version: 7d23a6b
// Last Update:   Mon Jan 12 18:01:58 2015 +0100
//

require_once __DIR__ . '/Underscore/Underscore.php';
require_once __DIR__ . '/Underscore/Bridge.php';

use Underscore\Underscore;
use Underscore\Bridge;

function _each($list, $iterator, $context=NULL)
{
	return Underscore::each($list, $iterator, $context=NULL);
}

function _eachReference(&$list, $iterator, $context=NULL)
{
	return Underscore::eachReference($list, $iterator, $context=NULL);
}

function _map($list, $iterator, $context=NULL)
{
	return Underscore::map($list, $iterator, $context=NULL);
}

function _reduce($list, $iterator, $memo, $context=NULL)
{
	return Underscore::reduce($list, $iterator, $memo, $context=NULL);
}

function _reduceRight($list, $iterator, $memo, $context=NULL)
{
	return Underscore::reduceRight($list, $iterator, $memo, $context=NULL);
}

function _find($list, $iterator, $context=NULL)
{
	return Underscore::find($list, $iterator, $context=NULL);
}

function _filter($list, $iterator=NULL, $context=NULL)
{
	return Underscore::filter($list, $iterator=NULL, $context=NULL);
}

function _where($list, $properties)
{
	return Underscore::where($list, $properties);
}

function _findWhere($list, $properties)
{
	return Underscore::findWhere($list, $properties);
}

function _reject($list, $iterator, $context=NULL)
{
	return Underscore::reject($list, $iterator, $context=NULL);
}

function _every($list, $iterator=NULL, $context=NULL)
{
	return Underscore::every($list, $iterator=NULL, $context=NULL);
}

function _some($list, $iterator=NULL, $context=NULL)
{
	return Underscore::some($list, $iterator=NULL, $context=NULL);
}

function _contains($list, $value, $strict=false)
{
	return Underscore::contains($list, $value, $strict=false);
}

function _invoke($list, $methodName, $arguments=[])
{
	return Underscore::invoke($list, $methodName, $arguments=[]);
}

function _pluck($list, $propertyName)
{
	return Underscore::pluck($list, $propertyName);
}

function _max($list, $iterator=NULL, $context=NULL)
{
	return Underscore::max($list, $iterator=NULL, $context=NULL);
}

function _min($list, $iterator=NULL, $context=NULL)
{
	return Underscore::min($list, $iterator=NULL, $context=NULL);
}

function _sortBy($list, $iterator, $context=NULL)
{
	return Underscore::sortBy($list, $iterator, $context=NULL);
}

function _indexBy($list, $iterator, $context=NULL)
{
	return Underscore::indexBy($list, $iterator, $context=NULL);
}

function _groupBy($list, $iterator, $context=NULL)
{
	return Underscore::groupBy($list, $iterator, $context=NULL);
}

function _countBy($list, $iterator, $context=NULL)
{
	return Underscore::countBy($list, $iterator, $context=NULL);
}

function _shuffle($list)
{
	return Underscore::shuffle($list);
}

function _sample($list, $n=1)
{
	return Underscore::sample($list, $n=1);
}

function _toArray($list)
{
	return Underscore::toArray($list);
}

function _size($list)
{
	return Underscore::size($list);
}

function _partition($list, $iterator, $context=NULL)
{
	return Underscore::partition($list, $iterator, $context=NULL);
}

function _first($array, $n=1, $guard=false)
{
	return Underscore::first($array, $n=1, $guard=false);
}

function _initial($array, $n=1, $guard=false)
{
	return Underscore::initial($array, $n=1, $guard=false);
}

function _last($array, $n=1, $guard=false)
{
	return Underscore::last($array, $n=1, $guard=false);
}

function _rest($array, $index=1, $guard=false)
{
	return Underscore::rest($array, $index=1, $guard=false);
}

function _compact($array)
{
	return Underscore::compact($array);
}

function _flatten($array, $shallow=false)
{
	return Underscore::flatten($array, $shallow=false);
}

function _without($array, $values)
{
	return Underscore::without($array, $values);
}

function _uniq($array, $isSorted=false, $iterator=NULL, $context=NULL)
{
	return Underscore::uniq($array, $isSorted=false, $iterator=NULL, $context=NULL);
}

function _union()
{
	return Underscore::union();
}

function _intersection($array)
{
	return Underscore::intersection($array);
}

function _difference($array)
{
	return Underscore::difference($array);
}

function _zip()
{
	return Underscore::zip();
}

function _obj($list, $values=NULL)
{
	return Underscore::obj($list, $values=NULL);
}

function _indexOf($array, $item)
{
	return Underscore::indexOf($array, $item);
}

function _lastIndexOf($array, $item)
{
	return Underscore::lastIndexOf($array, $item);
}

function _sortedIndex($array, $value, $iterator=NULL, $context=NULL)
{
	return Underscore::sortedIndex($array, $value, $iterator=NULL, $context=NULL);
}

function _range($start, $stop=NULL, $step=1)
{
	return Underscore::range($start, $stop=NULL, $step=1);
}

function _wrap($function, $wrapper)
{
	return Underscore::wrap($function, $wrapper);
}

function _compose($functions)
{
	return Underscore::compose($functions);
}

function _after($count, $function)
{
	return Underscore::after($count, $function);
}

function _once($function)
{
	return Underscore::once($function);
}

function _partial($function, $arguments)
{
	return Underscore::partial($function, $arguments);
}

function _bind($function, $object)
{
	return Underscore::bind($function, $object);
}

function _bindClass($function, $class)
{
	return Underscore::bindClass($function, $class);
}

function _bindAll($object, $methodNames)
{
	return Underscore::bindAll($object, $methodNames);
}

function _memoize($function, $hashFunction=NULL, &$cache=NULL)
{
	return Underscore::memoize($function, $hashFunction=NULL, $cache=NULL);
}

function _throttle($function, $wait)
{
	return Underscore::throttle($function, $wait);
}

function _keys($object)
{
	return Underscore::keys($object);
}

function _values($object)
{
	return Underscore::values($object);
}

function _pairs($object)
{
	return Underscore::pairs($object);
}

function _invert($object)
{
	return Underscore::invert($object);
}

function _functions($object)
{
	return Underscore::functions($object);
}

function _extend($destination, $sources)
{
	return Underscore::extend($destination, $sources);
}

function _pick($object, $keys)
{
	return Underscore::pick($object, $keys);
}

function _omit($object, $keys)
{
	return Underscore::omit($object, $keys);
}

function _defaults($object, $defaults)
{
	return Underscore::defaults($object, $defaults);
}

function _duplicate($object)
{
	return Underscore::duplicate($object);
}

function _tap($object, $interceptor)
{
	return Underscore::tap($object, $interceptor);
}

function _has($object, $key)
{
	return Underscore::has($object, $key);
}

function _get($object, $key, $default=NULL)
{
	return Underscore::get($object, $key, $default=NULL);
}

function _set(&$object, $key, $value)
{
	return Underscore::set($object, $key, $value);
}

function _is($object, $types)
{
	return Underscore::is($object, $types);
}

function _isEqual($object, $other)
{
	return Underscore::isEqual($object, $other);
}

function _isEmpty($object)
{
	return Underscore::isEmpty($object);
}

function _isArray($object, $native=false)
{
	return Underscore::isArray($object, $native=false);
}

function _isObject($object)
{
	return Underscore::isObject($object);
}

function _isFunction($object)
{
	return Underscore::isFunction($object);
}

function _isNumber($object, $native=false)
{
	return Underscore::isNumber($object, $native=false);
}

function _isInteger($object, $native=false)
{
	return Underscore::isInteger($object, $native=false);
}

function _isFloat($object, $native=false)
{
	return Underscore::isFloat($object, $native=false);
}

function _isString($object, $native=false)
{
	return Underscore::isString($object, $native=false);
}

function _isDate($object)
{
	return Underscore::isDate($object);
}

function _isRegExp($object)
{
	return Underscore::isRegExp($object);
}

function _isFinite($object)
{
	return Underscore::isFinite($object);
}

function _isNaN($object)
{
	return Underscore::isNaN($object);
}

function _isBoolean($object, $native=false)
{
	return Underscore::isBoolean($object, $native=false);
}

function _isNull($object)
{
	return Underscore::isNull($object);
}

function _isScalar($object, $native=false)
{
	return Underscore::isScalar($object, $native=false);
}

function _isTraversable($object)
{
	return Underscore::isTraversable($object);
}

function _isResource($object)
{
	return Underscore::isResource($object);
}

function _typeOf($object, $class=true)
{
	return Underscore::typeOf($object, $class=true);
}

function _identity($value)
{
	return Underscore::identity($value);
}

function _times($n, $iterator, $context=NULL)
{
	return Underscore::times($n, $iterator, $context=NULL);
}

function _random($min, $max=NULL)
{
	return Underscore::random($min, $max=NULL);
}

function _mixin($functions)
{
	return Underscore::mixin($functions);
}

function _provide($method)
{
	return Underscore::provide($method);
}

function _uniqueId($prefix='')
{
	return Underscore::uniqueId($prefix='');
}

function _escape($string)
{
	return Underscore::escape($string);
}

function _unescape($string)
{
	return Underscore::unescape($string);
}

function _result($object, $property)
{
	return Underscore::result($object, $property);
}

function _template($templateString, $data=[], $settings=[])
{
	return Underscore::template($templateString, $data, $settings);
}

function _chain($object)
{
	return Underscore::chain($object);
}

