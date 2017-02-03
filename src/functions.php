<?php

//    __  __          __                                            __
//   / / / /___  ____/ /__  ___________________  ________    ____  / /_  ____
//  / / / / __ \/ __  / _ \/ ___/ ___/ ___/ __ \/ ___/ _ \  / __ \/ __ \/ __ \
// / /_/ / / / / /_/ /  __/ /  (__  ) /__/ /_/ / /  /  __/ / /_/ / / / / /_/ /
// \____/_/ /_/\__,_/\___/_/  /____/\___/\____/_/   \___(_) .___/_/ /_/ .___/
//                                                       /_/         /_/
//
// Build Version: 0.3.0
// Last Update:   Fri, 03 Feb 2017 16:53:23 +0100
//

require_once __DIR__ . '/../vendor/autoload.php';

function _each(...$args)
{
    return Underscore\Underscore::each(...$args);
}

function _break(...$args)
{
    return Underscore\Underscore::break(...$args);
}

function _continue(...$args)
{
    return Underscore\Underscore::continue(...$args);
}

function _walk(...$args)
{
    return Underscore\Underscore::walk(...$args);
}

function _each_reference(...$args)
{
    return Underscore\Underscore::eachReference(...$args);
}

function _walk_recursive(...$args)
{
    return Underscore\Underscore::walkRecursive(...$args);
}

function _each_reference_recursive(...$args)
{
    return Underscore\Underscore::eachReferenceRecursive(...$args);
}

function _collect(...$args)
{
    return Underscore\Underscore::collect(...$args);
}

function _map(...$args)
{
    return Underscore\Underscore::map(...$args);
}

function _inject(...$args)
{
    return Underscore\Underscore::inject(...$args);
}

function _foldl(...$args)
{
    return Underscore\Underscore::foldl(...$args);
}

function _reduce(...$args)
{
    return Underscore\Underscore::reduce(...$args);
}

function _foldr(...$args)
{
    return Underscore\Underscore::foldr(...$args);
}

function _reduce_right(...$args)
{
    return Underscore\Underscore::reduceRight(...$args);
}

function _detect(...$args)
{
    return Underscore\Underscore::detect(...$args);
}

function _find(...$args)
{
    return Underscore\Underscore::find(...$args);
}

function _select(...$args)
{
    return Underscore\Underscore::select(...$args);
}

function _filter(...$args)
{
    return Underscore\Underscore::filter(...$args);
}

function _where(...$args)
{
    return Underscore\Underscore::where(...$args);
}

function _find_where(...$args)
{
    return Underscore\Underscore::findWhere(...$args);
}

function _reject(...$args)
{
    return Underscore\Underscore::reject(...$args);
}

function _all(...$args)
{
    return Underscore\Underscore::all(...$args);
}

function _every(...$args)
{
    return Underscore\Underscore::every(...$args);
}

function _any(...$args)
{
    return Underscore\Underscore::any(...$args);
}

function _some(...$args)
{
    return Underscore\Underscore::some(...$args);
}

function _includes(...$args)
{
    return Underscore\Underscore::includes(...$args);
}

function _contains(...$args)
{
    return Underscore\Underscore::contains(...$args);
}

function _invoke(...$args)
{
    return Underscore\Underscore::invoke(...$args);
}

function _pluck(...$args)
{
    return Underscore\Underscore::pluck(...$args);
}

function _max(...$args)
{
    return Underscore\Underscore::max(...$args);
}

function _min(...$args)
{
    return Underscore\Underscore::min(...$args);
}

function _sort_by(...$args)
{
    return Underscore\Underscore::sortBy(...$args);
}

function _index_by(...$args)
{
    return Underscore\Underscore::indexBy(...$args);
}

function _group_by(...$args)
{
    return Underscore\Underscore::groupBy(...$args);
}

function _count_by(...$args)
{
    return Underscore\Underscore::countBy(...$args);
}

function _shuffle(...$args)
{
    return Underscore\Underscore::shuffle(...$args);
}

function _sample(...$args)
{
    return Underscore\Underscore::sample(...$args);
}

function _to_array(...$args)
{
    return Underscore\Underscore::toArray(...$args);
}

function _size(...$args)
{
    return Underscore\Underscore::size(...$args);
}

function _partition(...$args)
{
    return Underscore\Underscore::partition(...$args);
}

function _head(...$args)
{
    return Underscore\Underscore::head(...$args);
}

function _take(...$args)
{
    return Underscore\Underscore::take(...$args);
}

function _first(...$args)
{
    return Underscore\Underscore::first(...$args);
}

function _initial(...$args)
{
    return Underscore\Underscore::initial(...$args);
}

function _last(...$args)
{
    return Underscore\Underscore::last(...$args);
}

function _tail(...$args)
{
    return Underscore\Underscore::tail(...$args);
}

function _drop(...$args)
{
    return Underscore\Underscore::drop(...$args);
}

function _rest(...$args)
{
    return Underscore\Underscore::rest(...$args);
}

function _compact(...$args)
{
    return Underscore\Underscore::compact(...$args);
}

function _flatten(...$args)
{
    return Underscore\Underscore::flatten(...$args);
}

function _without(...$args)
{
    return Underscore\Underscore::without(...$args);
}

function _unique(...$args)
{
    return Underscore\Underscore::unique(...$args);
}

function _uniq(...$args)
{
    return Underscore\Underscore::uniq(...$args);
}

function _union(...$args)
{
    return Underscore\Underscore::union(...$args);
}

function _intersection(...$args)
{
    return Underscore\Underscore::intersection(...$args);
}

function _difference(...$args)
{
    return Underscore\Underscore::difference(...$args);
}

function _zip(...$args)
{
    return Underscore\Underscore::zip(...$args);
}

function _obj(...$args)
{
    return Underscore\Underscore::obj(...$args);
}

function _index_of(...$args)
{
    return Underscore\Underscore::indexOf(...$args);
}

function _last_index_of(...$args)
{
    return Underscore\Underscore::lastIndexOf(...$args);
}

function _sorted_index(...$args)
{
    return Underscore\Underscore::sortedIndex(...$args);
}

function _range(...$args)
{
    return Underscore\Underscore::range(...$args);
}

function _xrange(...$args)
{
    return Underscore\Underscore::xrange(...$args);
}

function _wrap(...$args)
{
    return Underscore\Underscore::wrap(...$args);
}

function _negate(...$args)
{
    return Underscore\Underscore::negate(...$args);
}

function _compose(...$args)
{
    return Underscore\Underscore::compose(...$args);
}

function _after(...$args)
{
    return Underscore\Underscore::after(...$args);
}

function _before(...$args)
{
    return Underscore\Underscore::before(...$args);
}

function _once(...$args)
{
    return Underscore\Underscore::once(...$args);
}

function _partial(...$args)
{
    return Underscore\Underscore::partial(...$args);
}

function _bind(...$args)
{
    return Underscore\Underscore::bind(...$args);
}

function _bind_class(...$args)
{
    return Underscore\Underscore::bindClass(...$args);
}

function _bind_all(...$args)
{
    return Underscore\Underscore::bindAll(...$args);
}

function _memoize(...$args)
{
    return Underscore\Underscore::memoize(...$args);
}

function _throttle(...$args)
{
    return Underscore\Underscore::throttle(...$args);
}

function _call(...$args)
{
    return Underscore\Underscore::call(...$args);
}

function _apply(...$args)
{
    return Underscore\Underscore::apply(...$args);
}

function _keys(...$args)
{
    return Underscore\Underscore::keys(...$args);
}

function _values(...$args)
{
    return Underscore\Underscore::values(...$args);
}

function _pairs(...$args)
{
    return Underscore\Underscore::pairs(...$args);
}

function _invert(...$args)
{
    return Underscore\Underscore::invert(...$args);
}

function _methods(...$args)
{
    return Underscore\Underscore::methods(...$args);
}

function _functions(...$args)
{
    return Underscore\Underscore::functions(...$args);
}

function _extend(...$args)
{
    return Underscore\Underscore::extend(...$args);
}

function _pick(...$args)
{
    return Underscore\Underscore::pick(...$args);
}

function _omit(...$args)
{
    return Underscore\Underscore::omit(...$args);
}

function _defaults(...$args)
{
    return Underscore\Underscore::defaults(...$args);
}

function _copy(...$args)
{
    return Underscore\Underscore::copy(...$args);
}

function _duplicate(...$args)
{
    return Underscore\Underscore::duplicate(...$args);
}

function _tap(...$args)
{
    return Underscore\Underscore::tap(...$args);
}

function _has(...$args)
{
    return Underscore\Underscore::has(...$args);
}

function _property(...$args)
{
    return Underscore\Underscore::property(...$args);
}

function _matches(...$args)
{
    return Underscore\Underscore::matches(...$args);
}

function _get(...$args)
{
    return Underscore\Underscore::get(...$args);
}

function _set(...$args)
{
    return Underscore\Underscore::set(...$args);
}

function _is(...$args)
{
    return Underscore\Underscore::is(...$args);
}

function _is_equal(...$args)
{
    return Underscore\Underscore::isEqual(...$args);
}

function _is_empty(...$args)
{
    return Underscore\Underscore::isEmpty(...$args);
}

function _is_array(...$args)
{
    return Underscore\Underscore::isArray(...$args);
}

function _is_object(...$args)
{
    return Underscore\Underscore::isObject(...$args);
}

function _is_function(...$args)
{
    return Underscore\Underscore::isFunction(...$args);
}

function _is_num(...$args)
{
    return Underscore\Underscore::isNum(...$args);
}

function _is_numeric(...$args)
{
    return Underscore\Underscore::isNumeric(...$args);
}

function _is_number(...$args)
{
    return Underscore\Underscore::isNumber(...$args);
}

function _is_int(...$args)
{
    return Underscore\Underscore::isInt(...$args);
}

function _is_integer(...$args)
{
    return Underscore\Underscore::isInteger(...$args);
}

function _is_float(...$args)
{
    return Underscore\Underscore::isFloat(...$args);
}

function _is_string(...$args)
{
    return Underscore\Underscore::isString(...$args);
}

function _is_date(...$args)
{
    return Underscore\Underscore::isDate(...$args);
}

function _is_regexp(...$args)
{
    return Underscore\Underscore::isRegExp(...$args);
}

function _is_finite(...$args)
{
    return Underscore\Underscore::isFinite(...$args);
}

function _is_nan(...$args)
{
    return Underscore\Underscore::isNaN(...$args);
}

function _is_bool(...$args)
{
    return Underscore\Underscore::isBool(...$args);
}

function _is_boolean(...$args)
{
    return Underscore\Underscore::isBoolean(...$args);
}

function _is_null(...$args)
{
    return Underscore\Underscore::isNull(...$args);
}

function _is_scalar(...$args)
{
    return Underscore\Underscore::isScalar(...$args);
}

function _is_traversable(...$args)
{
    return Underscore\Underscore::isTraversable(...$args);
}

function _is_iterable(...$args)
{
    return Underscore\Underscore::isIterable(...$args);
}

function _is_resource(...$args)
{
    return Underscore\Underscore::isResource(...$args);
}

function _get_type(...$args)
{
    return Underscore\Underscore::getType(...$args);
}

function _type_of(...$args)
{
    return Underscore\Underscore::typeOf(...$args);
}

function _identity(...$args)
{
    return Underscore\Underscore::identity(...$args);
}

function _constant(...$args)
{
    return Underscore\Underscore::constant(...$args);
}

function _noop(...$args)
{
    return Underscore\Underscore::noop(...$args);
}

function _times(...$args)
{
    return Underscore\Underscore::times(...$args);
}

function _random(...$args)
{
    return Underscore\Underscore::random(...$args);
}

function _mixin(...$args)
{
    return Underscore\Underscore::mixin(...$args);
}

function _provide(...$args)
{
    return Underscore\Underscore::provide(...$args);
}

function _unique_id(...$args)
{
    return Underscore\Underscore::uniqueId(...$args);
}

function _escape(...$args)
{
    return Underscore\Underscore::escape(...$args);
}

function _unescape(...$args)
{
    return Underscore\Underscore::unescape(...$args);
}

function _result(...$args)
{
    return Underscore\Underscore::result(...$args);
}

function _lastly(...$args)
{
    return Underscore\Underscore::lastly(...$args);
}

function _now(...$args)
{
    return Underscore\Underscore::now(...$args);
}

function _template(...$args)
{
    return Underscore\Underscore::template(...$args);
}

function _chain(...$args)
{
    return Underscore\Underscore::chain(...$args);
}

function _strategy(...$args)
{
    return Underscore\Underscore::strategy(...$args);
}

function _forge(...$args)
{
    return Underscore\Underscore::forge(...$args);
}

