[![Build Status](https://travis-ci.org/bdelespierre/underscore.php.svg?branch=master)](https://travis-ci.org/bdelespierre/underscore.php)

&#187; <a href="#table-of-contents">Table of contents</a>

# Underscore.php

PHP lacks consistency, that's a fact. Many functions within a similar field — for instance array functions — may have inconsistent names and prototypes. Underscore.php aims to correct that by providing simple, consistent and data-type tolerant 80-odd functions that support both the usual functional suspects: map, select, invoke — as well as more specialized helpers: function binding, php templating, deep equality testing, and so on.

Underscore.php is __strongly__ inspired by [Underscore.js](http://underscorejs.org/) and try to be consistent with it as much as possible (PHP language limitation doesn't allow full coverage — especialy for Functions functions...) Don't hesitate to [report](https://github.com/bdelespierre/underscore.php/issues) any discrepancy with Underscore.js.

## Features

+ Made with &#9829; for PHP 5.4+
+ Type tolerant
+ Triggers exceptions instead of errors
+ Consistent function names / arguments
+ Hassle-free chaining
+ ERB-style templating
+ Extensible

## Heads up!

This library is in __beta__ phase: you are strongly encouraged to try it and to contribute.Feel free to [file an issue](https://github.com/bdelespierre/underscore.php/issues) if you encounter a bug or an unexpected result.

## About data-type tolerance

Juggling with types in PHP can be tedious. Not only types are sometimes ambiguous, they don't fit in every API function/method. For instance, if you want to map every item from an iterator using [array_map](http://php.net/manual/en/function.array-map.php), you have to translate it into an array first or write the mapping yourself using a loop. Same goes for [sort](http://php.net/manual/en/function.sort.php), [diff](http://php.net/manual/en/function.array-diff.php) or [filter](http://php.net/manual/en/function.array-filter.php)...

PHP is loosely typed, which means that the data you're manipulating are more important than their structure. Underscore.php understands that by providing a comprehensive interface that works with almost every data-type so you don't have to worry about whether you can or cannot use a function/method.

Basically, Underscore.php uses 3 main data-types:

+ Scalar (integer, float, boolean, or string)
+ Traversable (array, object, or iterator)
+ Callable (closure, function, method, or runtime-created function)

When a fuction requires a Traversable as argument, you can provide either an array, an instance of stdClass — the default `(object)` casting — an Iterator or anything that implements the Traversable interface, such as a PDOStatement object. With certain functions like [#extend](_::extend), you can even extend an array with an object instance and everything will be fine.

```PHP
<?php
// let's merge our configuration object with data from $_SESSION and MySQL
$userConfig = _::extend([],
	include "global.conf.php",              // is an array
	$pdo->query($groupConfigurationQuery)   // is a PDOStatement
	$_SESSION['user']->configuration,       // is an object
);
?>
```

# Table of contents

1. [Installation](#installation)
2. [Usage](#usage)
1. [Collection Functions](#collection-functions)
   * [each](#each), [eachReference](#eachreference), [map](#map), [reduce](#reduce), [reduceRight](#reduceright), [find](#find), [filter](#filter), [where](#where), [findWhere](#findwhere), [reject](#reject), [every](#every), [some](#some), [contains](#contains), [invoke](#invoke), [pluck](#pluck), [max](#max), [min](#min), [sortBy](#sortby), [indexBy](#indexby), [groupBy](#groupby), [countBy](#countby), [shuffle](#shuffle), [sample](#sample), [toArray](#toarray), [size](#size)
2. [Uncategorized](#uncategorized)
   * [partition](#partition), [now](#now)
3. [Array Functions](#array-functions)
   * [first](#first), [initial](#initial), [last](#last), [rest](#rest), [compact](#compact), [flatten](#flatten), [without](#without), [uniq](#uniq), [union](#union), [intersection](#intersection), [difference](#difference), [zip](#zip), [obj](#obj), [indexOf](#indexof), [lastIndexOf](#lastindexof), [sortedIndex](#sortedindex), [range](#range)
4. [Function (uh, ahem) Functions](#function-uh-ahem-functions)
   * [wrap](#wrap), [negate](#negate), [compose](#compose), [after](#after), [before](#before), [once](#once), [partial](#partial), [bind](#bind), [bindClass](#bindclass), [bindAll](#bindall), [memoize](#memoize), [throttle](#throttle), [call](#call), [apply](#apply)
5. [Object Functions](#object-functions)
   * [keys](#keys), [values](#values), [pairs](#pairs), [invert](#invert), [functions](#functions), [extend](#extend), [pick](#pick), [omit](#omit), [defaults](#defaults), [duplicate](#duplicate), [tap](#tap), [has](#has), [property](#property), [matches](#matches), [get](#get), [set](#set), [is](#is), [isEqual](#isequal), [isEmpty](#isempty), [isArray](#isarray), [isObject](#isobject), [isFunction](#isfunction), [isNumber](#isnumber), [isInteger](#isinteger), [isFloat](#isfloat), [isString](#isstring), [isDate](#isdate), [isRegExp](#isregexp), [isFinite](#isfinite), [isNaN](#isnan), [isBoolean](#isboolean), [isNull](#isnull), [isScalar](#isscalar), [isTraversable](#istraversable), [isResource](#isresource), [typeOf](#typeof)
6. [Utility Functions](#utility-functions)
   * [identity](#identity), [constant](#constant), [noop](#noop), [times](#times), [random](#random), [mixin](#mixin), [provide](#provide), [uniqueId](#uniqueid), [escape](#escape), [unescape](#unescape), [result](#result), [lastly](#lastly), [template](#template)
7. [Chaining](#chaining)
   * [chain](#chain)
8. [Class Forgery](#class-forgery)
   * [forge](#forge)

## Installation

##### *Composer*

Add the following require rule to composer.json and run `composer update`. See the [Packagist](https://packagist.org/packages/bdelespierre/underscore) repository for more details.

~~~
require: { "bdelespierre/underscore": "dev-master" }
~~~

##### *With Git*

~~~
git clone https://github.com/bdelespierre/underscore.php ./underscore.php
~~~

##### *Manual*

~~~
curl -sS https://github.com/bdelespierre/underscore.php/archive/master.zip > underscore.php.zip
unzip underscore.php.zip && rm underscore.zip
~~~

Or simply [download the zip](https://github.com/bdelespierre/underscore.php/archive/master.zip) and extract it where you want.

## Usage

##### *Composer*

```PHP
<?php
require_once "vendor/autoload.php";

use Underscore\Underscore as _;

_::each([1,2,3], function ($i) { echo "{$i}\n"; });
?>
```

##### *Manual*

```PHP
<?php
require_once "path/to/underscore/src/Underscore/Underscore.php";
require_once "path/to/underscore/src/Underscore/Bridge.php";

use Underscore\Underscore as _;

_::each([1,2,3], function ($i) { echo "{$i}\n"; });
?>
```

##### *Functions*

Underscore functions can also be used as procedural functions. To do so, include the `functions.php` library. The only limitation is that you cannot dynamically add new functions with `_::mixin`.

```PHP
<?php
require_once "path/to/underscore/src/functions.php";

_each([1,2,3], function ($i) { echo "{$i}\n"; });
?>
```

## Collection Functions
* [each](#each)
* [eachReference](#eachreference)
* [map](#map)
* [reduce](#reduce)
* [reduceRight](#reduceright)
* [find](#find)
* [filter](#filter)
* [where](#where)
* [findWhere](#findwhere)
* [reject](#reject)
* [every](#every)
* [some](#some)
* [contains](#contains)
* [invoke](#invoke)
* [pluck](#pluck)
* [max](#max)
* [min](#min)
* [sortBy](#sortby)
* [indexBy](#indexby)
* [groupBy](#groupby)
* [countBy](#countby)
* [shuffle](#shuffle)
* [sample](#sample)
* [toArray](#toarray)
* [size](#size)

### each
-----

_**Description**_: Iterates over a list of elements, yielding each in turn to an iterator function. The iterator is bound to the context object, if one is passed. Each invocation of iterator is called with three arguments: (element, index, list). If list is an object, iterator's arguments will be (value, key, list).

##### *Parameters*

+ *list*: traversable, the list to iterate over
+ *iterator*: callable, the iteration function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::each(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::each([1,2,3], function ($i) { echo $i; });
// => 123

_::each((object)['a'=>1,'b'=>2,'c'=>3], function ($value, $key) { echo "$key => $value\n"; });
// => displays each pair in turn
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### eachReference
-----

_**Alias**_: walk

_**Description**_: Does the very same job as each but provide a reference of every list item to the iterator function.

##### *Parameters*

+ *list*: traversable, the list to iterate over
+ *iterator*: callable, the iteration function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::eachReference(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$numbers = [1,2,3];
_::eachReference($numbers, function (& $value) { $value *= $value; });
// => [1,4,9]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### map
-----

_**Alias**_: collect

_**Description**_: Produces a new array of values by mapping each value in list through a transformation function (iterator). The iterator is bound to the context object, if one is passed. Each invocation of iterator is called with three arguments: (element, index, list). If list is an object, iterator's arguments will be (value, key, list).

##### *Parameters*

+ *list*: traversable, the list of items to map
+ *iterator*: callable, the transformation function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::map(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::map([1,2,3], function ($value) { return $value -1; });
// => [0,1,2]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### reduce
-----

_**Alias**_: inject, foldl

_**Description**_: Also known as inject and foldl, reduce boils down a list of values into a single value. Memo is the initial state of the reduction, and each successive step of it should be returned by iterator. The iterator is passed four arguments: the memo, then the value and index (or key) of the iteration, and finally a reference to the entire list.

##### *Parameters*

+ *list*: traversable, the list of items to reduce
+ *iterator*: callable, the reduction function
+ *memo*: mixed, The initial reduction state
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::reduce(list,iterator,memo,context)
~~~

##### *Examples*

```PHP
<?php
$sum = _::reduce([1,2,3], function ($memo, $num) { return $memo + $num; }, 0);
// => 6
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### reduceRight
-----

_**Alias**_: foldr

_**Description**_: The right-associative version of reduce.

##### *Parameters*

+ *list*: traversable, the list of items to reduce
+ *iterator*: callable, the reduction function
+ *memo*: mixed, The initial reduction state
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::reduceRight(list,iterator,memo,context)
~~~

##### *Examples*

```PHP
<?php
$list = [[0, 1], [2, 3], [4, 5]];
$flat = _::reduceRight($list, function ($a, $b) { return array_merge($a, $b); }, []);
// => [4,5,2,3,0,1]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### find
-----

_**Alias**_: detect

_**Description**_: Looks through each value in the list, returning the first one that passes a truth test (iterator), or null if no value passes the test. The function returns as soon as it finds an acceptable element, and doesn't traverse the entire list.

##### *Parameters*

+ *list*: traversable, the list of items to iterate over
+ *iterator*: callable, the truth-test function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::find(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$even = _::find([1,2,3,4,5,6], function ($num) { return $num % 2 == 0; });
// => 2
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### filter
-----

_**Alias**_: select

_**Description**_: Looks through each value in the list, returning an array of all the values that pass a truth test (iterator). If iterator isn't provided, each value will be evaluated as a boolean.

##### *Parameters*

+ *list*: traversable, the list of items to filter
+ *iterator*: callable, the filtering function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::filter(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$evens = _::filter([1,2,3,4,5,6], function ($num) { return $num % 2 == 0; });
// => [2,4,6]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### where
-----

_**Description**_: Looks through each value in the list, returning an array of all the values that contain all of the key-value pairs listed in properties.

##### *Parameters*

+ *list*: traversable, the list of items to filter
+ *properties*: traversable, the key-values pairs each filtered item must match

##### *Prototype*

~~~
_::where(list,properties)
~~~

##### *Examples*

```PHP
<?php
$people = [
    ['name' => 'Jack Nicholson',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Morgan Freeman',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Leonardo Dicaprio', 'born' => 1974, 'profession' => 'actor'],
    ['name' => 'Nathalie Portman',  'born' => 1981, 'profession' => 'actor'],
    ['name' => 'Ridley Scott',      'born' => 1937, 'profession' => 'producer'],
];

$actorsBornIn1937 = _::where($people, ['born' => 1937, 'profession' => 'actor']);
// => Jack Nicholson & Morgan Freeman
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### findWhere
-----

_**Description**_: Looks through the list and returns the first value that matches all of the key-value pairs listed in properties.

##### *Parameters*

+ *list*: traversable, the list of items to filter
+ *properties*: traversable, the key-values pairs each filtered item must match

##### *Prototype*

~~~
_::findWhere(list,properties)
~~~

##### *Examples*

```PHP
<?php
$people = [
    ['name' => 'Jack Nicholson',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Morgan Freeman',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Leonardo Dicaprio', 'born' => 1974, 'profession' => 'actor'],
    ['name' => 'Nathalie Portman',  'born' => 1981, 'profession' => 'actor'],
    ['name' => 'Ridley Scott',      'born' => 1937, 'profession' => 'producer'],
];

$actor = _::findWhere($people, ['born' => 1937, 'profession' => 'actor']);
// => Jack Nicholsonn
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### reject
-----

_**Description**_: Returns the values in list without the elements that the truth test (iterator) passes. The opposite of filter.

##### *Parameters*

+ *list*: traversable, the list of items to filter
+ *iterator*: callable, the truth-test function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::reject(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$odds = _::reject([1,2,3,4,5,6], function ($num) { return $num % 2 == 0; });
// => [1,3,5]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### every
-----

_**Alias**_: all

_**Description**_: Returns true if all of the values in the list pass the iterator truth test. Short-circuits and stops traversing the list if a false element is found.

##### *Parameters*

+ *list*: traversable, the list of items to filter
+ *iterator*: callable, the truth-test function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::every(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::every([true, 1, null, 'yes']);
// => false
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### some
-----

_**Alias**_: any

_**Description**_: Returns true if any of the values in the list pass the iterator truth test. Short-circuits and stops traversing the list if a true element is found.

##### *Parameters*

+ *list*: traversable, the list of items to filter
+ *iterator*: callable, the truth-test function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::some(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::some([null, 0, 'yes', false]);
// => true
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### contains
-----

_**Alias**_: includes

_**Description**_: Returns true if the value is present in the list.

##### *Parameters*

+ *list*: traversable, the list of items
+ *value*: mixed, the value to look for
+ *strict*: boolean, type of value is also used in coparision

##### *Prototype*

~~~
_::contains(list,value,strict)
~~~

##### *Examples*

```PHP
<?php
_::contains([1,2,3], 3);
// => true
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### invoke
-----

_**Description**_: Calls the method named by methodName on each value in the list. Any extra arguments passed to invoke will be forwarded on to the method invocation. If your list items are arrays (instead of objects) methods from ArrayObject can be used (like asort). If the wanted method is not found on the current item during iteration, the item will be left untouched.

##### *Parameters*

+ *list*: traversable, the list of items to invoke method/function onto
+ *methodName*: callable,string, the name of the method to invoke or a closure
+ *arguments*: array, the method's arguments

##### *Prototype*

~~~
_::invoke(list,methodName,arguments)
~~~

##### *Examples*

```PHP
<?php
_::invoke([[5, 1, 7], [3, 2, 1]], 'sort');
// => [[1, 5, 7], [1, 2, 3]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### pluck
-----

_**Description**_: A convenient version of what is perhaps the most common use-case for map: extracting a list of property values.

##### *Parameters*

+ *list*: traversable, the list of items
+ *propertyName*: string, the name of the property to extract from each item

##### *Prototype*

~~~
_::pluck(list,propertyName)
~~~

##### *Examples*

```PHP
<?php
$stooges = [
    ['name' => 'moe',   'age' => 40],
    ['name' => 'larry', 'age' => 50],
    ['name' => 'curly', 'age' => 60]
];
_::pluck($stooges, 'name');
// => ['moe','larry','curly']
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### max
-----

_**Description**_: Returns the maximum value in list. If iterator is passed, it will be used on each value to generate the criterion by which the value is ranked.

##### *Parameters*

+ *list*: traversable, the list of items
+ *iterator*: callable, optional, the comparision function
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::max(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$stooges = [
    ['name' => 'moe',   'age' => 40],
    ['name' => 'larry', 'age' => 50],
    ['name' => 'curly', 'age' => 60]
];
_::max($stooges, function($stooge) { return $stooge['age']; });
// => 60
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### min
-----

_**Description**_: Returns the minimum value in list. If iterator is passed, it will be used on each value to generate the criterion by which the value is ranked.

##### *Parameters*

+ *list*: traversable, the list of items
+ *iterator*: mixed, no description available...
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::min(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$numbers = [10, 5, 100, 2, 10000];
_::min($numbers);
// => 2
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### sortBy
-----

_**Description**_: Returns a (stably) sorted copy of list, ranked in ascending order by the results of running each value through iterator. Returns NULL in case of error.

##### *Parameters*

+ *list*: traversable, the list of items to sort
+ *iterator*: callable, the function that generates the criteria by which items are sorted
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::sortBy(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::sortBy([1, 2, 3, 4, 5, 6], function($num) { return sin($num); });
// => [5, 4, 6, 3, 1, 2]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### indexBy
-----

_**Description**_: Given a list, and an iterator function that returns a key for each element in the list (or a property name), returns an object with an index of each item. Just like groupBy, but for when you know your keys are unique.

##### *Parameters*

+ *list*: traversable, the list of items to index
+ *iterator*: callable,scalar, the function to generate the key or a property name
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::indexBy(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
$stooges = [
    ['name' => 'moe',   'age' => 40],
    ['name' => 'larry', 'age' => 50],
    ['name' => 'curly', 'age' => 60]
];
_::indexBy($stooges, 'age');
// => [
//     "40" => ['name' => 'moe',   'age' => 40],
//     "50" => ['name' => 'larry', 'age' => 50],
//     "60" => ['name' => 'curly', 'age' => 60]
// ]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### groupBy
-----

_**Description**_: Splits a collection into sets, grouped by the result of running each value through iterator. If iterator is a string instead of a function, groups by the property named by iterator on each of the values.

##### *Parameters*

+ *list*: traversable, the list of items to group
+ *iterator*: callable,scalar, the function to generate the key or a property name
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::groupBy(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::groupBy([1.3, 2.1, 2.4], function($num) { return floor($num); });
// => [1 => [1.3], 2 => [2.1, 2.4]]

$values = [
    ['val' => 'one',   'length' => 3],
    ['val' => 'two',   'length' => 3],
    ['val' => 'three', 'length' => 5]
];
_::groupBy($values, 'length');
// => [3 => [['val' => 'one', 'lenght' => 3], ['val' => 'two', 'length' => 3], 5 => [['val' => 'three', 'length' => 5]]]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### countBy
-----

_**Description**_: Sorts a list into groups and returns a count for the number of objects in each group. Similar to groupBy, but instead of returning a list of values, returns a count for the number of values in that group.

##### *Parameters*

+ *list*: traversable, the list of items to group and count
+ *iterator*: callable,scalar, the function to generate the key or a property name
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::countBy(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::countBY([1, 2, 3, 4, 5], function($num) {
    return $num % 2 == 0 ? 'even' : 'odd';
});
// => ['odd' => 3, 'even' => 2]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### shuffle
-----

_**Description**_: Returns a shuffled copy of the list.

##### *Parameters*

+ *list*: traversable, the list of items to shuffle

##### *Prototype*

~~~
_::shuffle(list)
~~~

##### *Examples*

```PHP
<?php
_::shuffle([1, 2, 3, 4, 5, 6]);
// => [4, 1, 6, 3, 5, 2]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### sample
-----

_**Description**_: Produce a random sample from the list. Pass a number to return n random elements from the list. Otherwise a single random item will be returned.

##### *Parameters*

+ *list*: traversable, the list of items
+ *n*: int, optional, the number of items to pick

##### *Prototype*

~~~
_::sample(list,n)
~~~

##### *Examples*

```PHP
<?php
_::sample([1, 2, 3, 4, 5, 6]);
// => 4

_::sample([1, 2, 3, 4, 5, 6], 3);
// => [1, 6, 2]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### toArray
-----

_**Description**_: Creates a real Array from the list (anything that can be iterated over). This method will also accept scalars such as string, number and even null and will *cast* them into arrays, for instance Underscore::toArray(null) is [] altough Underscore::toArray('a') is ['a'].

##### *Parameters*

+ *list*: traversable, the list of items

##### *Prototype*

~~~
_::toArray(list)
~~~

##### *Examples*

```PHP
<?php
$object = new stdClass;
$object->one   = 1;
$object->two   = 2;
$object->three = 3;
_::toArray($object);
// => ['one' => 1, 'two' => 2, 'three' => 3]

_::toArray(null);
// => []

_::toArray("hello");
// => ["hello"]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

### size
-----

_**Description**_: Return the number of values in the list. This method will also accept scalars such as string, number and even null or resources but will return 1 in that case.

##### *Parameters*

+ *list*: traversable, the list of items

##### *Prototype*

~~~
_::size(list)
~~~

##### *Examples*

```PHP
<?php
$object = new stdClass;
$object->one   = 1;
$object->two   = 2;
$object->three = 3;
_::size($object);
// => 3
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#collection-functions">Collection Functions</a></p>

## Uncategorized
* [partition](#partition)
* [now](#now)

### partition
-----

_**Description**_: Split a collection into two arrays: one whose elements all satisfy the given predicate, and one whose elements all do not satisfy the predicate.

##### *Parameters*

+ *list*: traversable, the list of items
+ *iterator*: callable, the predicate
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::partition(list,iterator,context)
~~~

##### *Examples*

```PHP
<?php
_::partition([0, 1, 2, 3, 4, 5], function($num) { return $num % 2 != 0; });
// => [[1, 3, 5], [0, 2, 4]]
?>
```

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#uncategorized">Uncategorized</a></p>

### now
-----

_**Description**_: Returns an integer timestamp for the current time.

##### *Parameters*


##### *Prototype*

~~~
_::now()
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#uncategorized">Uncategorized</a></p>

## Array Functions
* [first](#first)
* [initial](#initial)
* [last](#last)
* [rest](#rest)
* [compact](#compact)
* [flatten](#flatten)
* [without](#without)
* [uniq](#uniq)
* [union](#union)
* [intersection](#intersection)
* [difference](#difference)
* [zip](#zip)
* [obj](#obj)
* [indexOf](#indexof)
* [lastIndexOf](#lastindexof)
* [sortedIndex](#sortedindex)
* [range](#range)

### first
-----

_**Alias**_: head, take

_**Description**_: Returns the first element of an array. Passing n will return the first n elements of the array. Passing guard will force the returned value to be an array.

##### *Parameters*

+ *array*: traversable, the list of items
+ *n*: int, optional, the number of items to pick
+ *guard*: bool, optional, true to always return an array

##### *Prototype*

~~~
_::first(array,n,guard)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### initial
-----

_**Description**_: Returns everything but the last entry of the array. Pass n to exclude the last n elements from the result. Passing guard will force the returned value to be an array.

##### *Parameters*

+ *array*: traversable, the list of items
+ *n*: int, optional, the number of items to exclude
+ *guard*: bool, optional, true to always return an array

##### *Prototype*

~~~
_::initial(array,n,guard)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### last
-----

_**Description**_: Returns the last element of an array. Passing n will return the last n elements of the array. Passing guard will force the returned value to be an array.

##### *Parameters*

+ *array*: traversabel, the list of items
+ *n*: int, optional, the number of items to pick
+ *guard*: bool, optional, true to always return an array

##### *Prototype*

~~~
_::last(array,n,guard)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### rest
-----

_**Alias**_: tail, drop

_**Description**_: Returns the rest of the elements in an array. Pass an index to return the values of the array from that index onward. Passing guard will force the returned value to be an array.

##### *Parameters*

+ *array*: traversable, the list of items
+ *index*: int, optional, the index from which the items are picked
+ *guard*: bool, optional, true to always return an array

##### *Prototype*

~~~
_::rest(array,index,guard)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### compact
-----

_**Description**_: Returns a copy of the array with all falsy values removed. In PHP, false, null, 0, "", array() and "0" are all falsy.

##### *Parameters*

+ *array*: traversable, the list of items

##### *Prototype*

~~~
_::compact(array)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### flatten
-----

_**Description**_: Flattens a nested array (the nesting can be to any depth). If you pass shallow, the array will only be flattened a single level.

##### *Parameters*

+ *array*: traversable, the list of items
+ *shallow*: bool, optional, if true will only flatten on single level

##### *Prototype*

~~~
_::flatten(array,shallow)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### without
-----

_**Description**_: Returns a copy of the array with all instances of the values removed.

##### *Parameters*

+ *array*: traversable, the list of items
+ *values*: array,mixed, multiple, the value(s) to exclude

##### *Prototype*

~~~
_::without(array,values)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### uniq
-----

_**Alias**_: unique

_**Description**_: Produces a duplicate-free version of the array, using === to test object equality. If you know in advance that the array is sorted, passing true for isSorted will run a much faster algorithm. If you want to compute unique items based on a transformation, pass an iterator function. WARNING: this function's cyclomatic complexity is (at least) quadratic ! using it with large arrays (> 1000 items) can be very slow and memory consuming.

##### *Parameters*

+ *array*: traversable, the list of items
+ *isSorted*: bool, optional, use a faster algorithm if the list is already sorted
+ *iterator*: callable, optional, the comparision function if needed
+ *context*: object, optional, if provided will become the context of $iterator

##### *Prototype*

~~~
_::uniq(array,isSorted,iterator,context)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### union
-----

_**Description**_: Computes the union of the passed-in arrays: the list of unique items, in order, that are present in one or more of the arrays.

##### *Parameters*

+ *array*: traversable, multiple, the arrays to join

##### *Prototype*

~~~
_::union(array)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### intersection
-----

_**Description**_: Computes the list of values that are the intersection of all the arrays. Each value in the result is present in each of the arrays.

##### *Parameters*

+ *array*: traversable, multiple, the arrays to intersect

##### *Prototype*

~~~
_::intersection(array)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### difference
-----

_**Description**_: Similar to without, but returns the values from array that are not present in the other arrays.

##### *Parameters*

+ *array*: traversable, multiple, the arrays to difference

##### *Prototype*

~~~
_::difference(array)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### zip
-----

_**Description**_: Merges together the values of each of the arrays with the values at the corresponding position. Useful when you have separate data sources that are coordinated through matching array indexes.

##### *Parameters*

+ *array*: traversable, multiple, the arrays to zip

##### *Prototype*

~~~
_::zip(array)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### obj
-----

_**Description**_: Converts arrays into objects. Pass either a single list of [key, value] pairs, or a list of keys, and a list of values. If duplicate keys exist, the last value wins.

##### *Parameters*

+ *list*: array, the properties
+ *values*: array, optional, the values, if not provided each item of $list is used a pair

##### *Prototype*

~~~
_::obj(list,values)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### indexOf
-----

_**Description**_: Returns the index at which value can be found in the array, or -1 if value is not present in the array. This method uses array_search internally and is not optimized for long array binary search.

##### *Parameters*

+ *array*: traversable, the list of items
+ *item*: mixed, the value to look for

##### *Prototype*

~~~
_::indexOf(array,item)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### lastIndexOf
-----

_**Description**_: Returns the index of the last occurrence of value in the array, or -1 if value is not present. This method uses array_keys internally and is not optimized for long array binary search.

##### *Parameters*

+ *array*: traversable, the list of items
+ *item*: mixed, the value to look for

##### *Prototype*

~~~
_::lastIndexOf(array,item)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### sortedIndex
-----

_**Description**_: Uses a binary search to determine the index at which the value should be inserted into the list in order to maintain the list's sorted order. If an iterator is passed, it will be used to compute the sort ranking of each value, including the value you pass. Iterator may also be the string name of the property to sort by (eg. length).

##### *Parameters*

+ *array*: traversable, the list of items
+ *value*: mixed, the value to find the index for
+ *iterator*: callable,scalar, optional, the function by which a value is evaluated or a property's name
+ *context*: object, optional, if provided will become the context for $iterator

##### *Prototype*

~~~
_::sortedIndex(array,value,iterator,context)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

### range
-----

_**Description**_: A function to create flexibly-numbered lists of integers, handy for each and map loops. start, if omitted, defaults to 0; step defaults to 1. Returns a list of integers from start to stop, incremented (or decremented) by step, exclusive. This method uses range internally.

##### *Parameters*

+ *start*: int, the starting index
+ *stop*: int, optional, the ending index
+ *step*: int, optional, the iteration step

##### *Prototype*

~~~
_::range(start,stop,step)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#array-functions">Array Functions</a></p>

## Function (uh, ahem) Functions
* [wrap](#wrap)
* [negate](#negate)
* [compose](#compose)
* [after](#after)
* [before](#before)
* [once](#once)
* [partial](#partial)
* [bind](#bind)
* [bindClass](#bindclass)
* [bindAll](#bindall)
* [memoize](#memoize)
* [throttle](#throttle)
* [call](#call)
* [apply](#apply)

### wrap
-----

_**Description**_: Wrap the first function inside of the wrapper function, passing it as the first argument. This allow the wrapper to execute code before and after the function runs, adjust the arguments and execute it conditionnaly. Arguments are passed along to the wrapper function.

##### *Parameters*

+ *function*: callable, the function
+ *wrapper*: mixed, no description available...

##### *Prototype*

~~~
_::wrap(function,wrapper)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### negate
-----

_**Description**_: Returns a new negated version of the predicate function.

##### *Parameters*

+ *function*: callable, the function

##### *Prototype*

~~~
_::negate(function)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### compose
-----

_**Description**_: Returns the composition of a list of functions, where each function consumes the return value of the function that follows. In math terms, composing the functions f(), g(), and h() produces f(g(h())).

##### *Parameters*

+ *functions*: callable, multiple, the functions to compose

##### *Prototype*

~~~
_::compose(functions)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### after
-----

_**Description**_: Creates a version of the function that will only be run after first being called count times. Please note that the function shall not recieve parameters.

##### *Parameters*

+ *count*: int, the number of times the $function shall be executed
+ *function*: callable, the function

##### *Prototype*

~~~
_::after(count,function)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### before
-----

_**Description**_: Creates a version of the function that can be called no more than count times. The result of the last function call is memoized and returned when count has been reached.

##### *Parameters*

+ *count*: int, the number of times the $function shall be executed
+ *function*: callable, the function

##### *Prototype*

~~~
_::before(count,function)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### once
-----

_**Description**_: Creates a version of the function that can only be called one time. Repeated calls to the modified function will have no effect, returning the value from the original call. Useful for initialization functions, instead of having to set a boolean flag and then check it later.

##### *Parameters*

+ *function*: callable, the function

##### *Prototype*

~~~
_::once(function)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### partial
-----

_**Description**_: Partially apply a function by filling in any number of its arguments. Not all the arguments have to be present on the partial construction.

##### *Parameters*

+ *function*: callable, the function
+ *arguments*: array,mixed, multiple, the arguments

##### *Prototype*

~~~
_::partial(function,arguments)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### bind
-----

_**Description**_: Bind a function to an object, meaning that whenever the function is called, the value of $this will be the object. Optionally, pass arguments to the function to pre-fill them, also known as partial application.

##### *Parameters*

+ *function*: closure, the function
+ *object*: object, the object to bind the closure to

##### *Prototype*

~~~
_::bind(function,object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### bindClass
-----

_**Description**_: Bind a function to a class, meaning that whenever the function is called, the value of self or static will be the class. Optionally, pass arguments to the function to pre-fill them, also known as partial application.

##### *Parameters*

+ *function*: closure, the function
+ *class*: object,string, the object or classname to bind the closure to

##### *Prototype*

~~~
_::bindClass(function,class)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### bindAll
-----

_**Description**_: Binds a number of methods on the object, specified by methodNames, to be run in the context of that object whenever they are invoked. Very handy for binding functions that are going to be used as event handlers, which would otherwise be invoked with a fairly useless this. methodNames are required. Keep in mind PHP doesn't allow to call a closure property value like a method, for instance $o->myClosure(), given $o is an instance of stdClass, won't work.

##### *Parameters*

+ *object*: object, the object
+ *methodNames*: array,callable, multiple, the functions to attach

##### *Prototype*

~~~
_::bindAll(object,methodNames)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### memoize
-----

_**Description**_: Memoizes a given function by caching the computed result. Useful for speeding up slow-running computations. If passed an optional hashFunction, it will be used to compute the hash key for storing the result, based on the arguments to the original function. The default hashFunction just uses the first argument to the memoized function as the key.

##### *Parameters*

+ *function*: callable, the function to memoize
+ *hashFunction*: callable, optional, if provided will be used to hash $function's results
+ *cache*: array,ArrayAccess, optional, function's results cache

##### *Prototype*

~~~
_::memoize(function,hashFunction,cache)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### throttle
-----

_**Description**_: Creates and returns a new, throttled version of the passed function, that, when invoked repeatedly, will only actually call the original function at most once per every wait milliseconds. Useful for rate-limiting events that occur faster than you can keep up with.

##### *Parameters*

+ *function*: callable, the function
+ *wait*: int, the time to wait between each call (in milliseconds)

##### *Prototype*

~~~
_::throttle(function,wait)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### call
-----

_**Description**_: Call (execute) the given function, optionnaly bound to $context, with the given arguments and return its result.

##### *Parameters*

+ *function*: callable, the function
+ *context*: object, the function's context

##### *Prototype*

~~~
_::call(function,context)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

### apply
-----

_**Description**_: Call (execute) the given function, optionnaly bound to $context, with the given argument list and return its result.

##### *Parameters*

+ *function*: callable, the function
+ *context*: object, the function's context
+ *arguments*: list, the arguments

##### *Prototype*

~~~
_::apply(function,context,arguments)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#function-uh-ahem-functions">Function (uh, ahem) Functions</a></p>

## Object Functions
* [keys](#keys)
* [values](#values)
* [pairs](#pairs)
* [invert](#invert)
* [functions](#functions)
* [extend](#extend)
* [pick](#pick)
* [omit](#omit)
* [defaults](#defaults)
* [duplicate](#duplicate)
* [tap](#tap)
* [has](#has)
* [property](#property)
* [matches](#matches)
* [get](#get)
* [set](#set)
* [is](#is)
* [isEqual](#isequal)
* [isEmpty](#isempty)
* [isArray](#isarray)
* [isObject](#isobject)
* [isFunction](#isfunction)
* [isNumber](#isnumber)
* [isInteger](#isinteger)
* [isFloat](#isfloat)
* [isString](#isstring)
* [isDate](#isdate)
* [isRegExp](#isregexp)
* [isFinite](#isfinite)
* [isNaN](#isnan)
* [isBoolean](#isboolean)
* [isNull](#isnull)
* [isScalar](#isscalar)
* [isTraversable](#istraversable)
* [isResource](#isresource)
* [typeOf](#typeof)

### keys
-----

_**Description**_: Retrieve all the names of the object's properties.

##### *Parameters*

+ *object*: traversable, the list from which the keys are extracted

##### *Prototype*

~~~
_::keys(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### values
-----

_**Description**_: Return all of the values of the object's properties.

##### *Parameters*

+ *object*: traversable, the list from which the values are extracted

##### *Prototype*

~~~
_::values(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### pairs
-----

_**Description**_: Convert an object into a list of [key, value] pairs.

##### *Parameters*

+ *object*: traversable, the list of items to convert to pairs

##### *Prototype*

~~~
_::pairs(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### invert
-----

_**Description**_: Returns a copy of the object where the keys have become the values and the values the keys. For this to work, all of your object's values should be unique and string serializable.

##### *Parameters*

+ *object*: traversable, the object to invert

##### *Prototype*

~~~
_::invert(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### functions
-----

_**Alias**_: methods

_**Description**_: Returns a sorted list of the names of every method in an object — that is to say, the name of every function property of the object.

##### *Parameters*

+ *object*: traversable,object, the object to extract the functions from

##### *Prototype*

~~~
_::functions(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### extend
-----

_**Description**_: Copy all of the properties in the source objects over to the destination object, and return the destination object. It's in-order, so the last source will override properties of the same name in previous arguments.

##### *Parameters*

+ *destination*: object,array, the destination object
+ *sources*: object,array, multiple, the source objects

##### *Prototype*

~~~
_::extend(destination,sources)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### pick
-----

_**Description**_: Returns a copy of the object, filtered to only have values for the whitelisted keys (or array of valid keys). If provided object is an object (in the broadest sense), a stdClass instance is returned, otherwise an array is returned.

##### *Parameters*

+ *object*: traversable, the object to pick properties on
+ *keys*: array,scalar, multiple, the keys to pick

##### *Prototype*

~~~
_::pick(object,keys)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### omit
-----

_**Description**_: Return a copy of the object, filtered to omit the blacklisted keys (or array of keys). If provided object is an object (in the broadest sense), a stdClass instance is returned, otherwise an array is returned.

##### *Parameters*

+ *object*: traversable, the object to exclude keys from
+ *keys*: array,scalar, multiple, the keys to omit

##### *Prototype*

~~~
_::omit(object,keys)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### defaults
-----

_**Description**_: Fill in null properties in object with values from the defaults objects, and return the object. As soon as the property is filled, further defaults will have no effect.

##### *Parameters*

+ *object*: traversable, the object to fill
+ *defaults*: traversable, multiple, the objects or array that will fill object's missing keys

##### *Prototype*

~~~
_::defaults(object,defaults)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### duplicate
-----

_**Alias**_: copy

_**Description**_: Create a shallow-copied clone of the object. Any nested objects or arrays will be copied by reference, not duplicated. This method is safe to use with arrays.

##### *Parameters*

+ *object*: traversable, the object to clone

##### *Prototype*

~~~
_::duplicate(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### tap
-----

_**Description**_: Invokes interceptor with the object, and then returns object. The primary purpose of this method is to "tap into" a method chain, in order to perform operations on intermediate results within the chain.

##### *Parameters*

+ *object*: mixed, the object
+ *interceptor*: callable, the function to call with the object as parameter

##### *Prototype*

~~~
_::tap(object,interceptor)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### has
-----

_**Description**_: Tells whether the object has a non null value for the given key. Gives priority to array's getters ($obj[$key] priorityze on $obj->$key). 'null' is equivalent to 'undefined'.

##### *Parameters*

+ *object*: object,array, the object
+ *key*: scalar, the key

##### *Prototype*

~~~
_::has(object,key)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### property
-----

_**Description**_: Returns a function that will itself return the key property of any passed-in object.

##### *Parameters*

+ *key*: string,int, the key or offset to get

##### *Prototype*

~~~
_::property(key)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### matches
-----

_**Description**_: Returns a predicate function that will tell you if a passed in object contains all of the key/value properties present in properties.

##### *Parameters*

+ *properties*: traversable, the properties used by predicate

##### *Prototype*

~~~
_::matches(properties)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### get
-----

_**Description**_: Get the object's key value. If such keys doesn't exists, the default value is returned. If object is neither Array nor an Object, the object itself is returned.

##### *Parameters*

+ *object*: object,array, the object
+ *key*: scalar, the key
+ *default*: mixed, optional, the default value to return in case the key doesn't exists

##### *Prototype*

~~~
_::get(object,key,default)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### set
-----

_**Description**_: Set object's key value. If object is neither Array nor an Object, the object itself is returned.

##### *Parameters*

+ *object*: mixed, reference, the object
+ *key*: scalar, the key
+ *value*: mixed, the value to set

##### *Prototype*

~~~
_::set(object,key,value)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### is
-----

_**Description**_: Tells whether the object is of the given type, or class, or pseudo-type. You may pass several types at once (using an array of types or by passing several types as arguments), Underscore::is will return true if object matchs any of these.

##### *Parameters*

+ *object*: mixed, the object
+ *types*: string, the types to test

##### *Prototype*

~~~
_::is(object,types)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isEqual
-----

_**Description**_: Performs an optimized deep comparison between the two objects, to determine if they should be considered equal.

##### *Parameters*

+ *object*: mixed, the first object
+ *other*: mixed, the second object

##### *Prototype*

~~~
_::isEqual(object,other)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isEmpty
-----

_**Description**_: Returns true if object contains no values (no enumerable own-properties). Works with scalars as well.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isEmpty(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isArray
-----

_**Description**_: Returns true if object is an array or usable like an array. If the optionnal native parameter is set to true, it will only return true if object is a native array.

##### *Parameters*

+ *object*: mixed, the object
+ *native*: bool, optional, if true will no consider instances of ArrayAccess as arrays

##### *Prototype*

~~~
_::isArray(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isObject
-----

_**Description**_: Returns true if value is an Object.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isObject(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isFunction
-----

_**Description**_: Returns true if object is a Function.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isFunction(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isNumber
-----

_**Alias**_: isNum, isNumeric

_**Description**_: Returns true of object is a Number. If the optionnal native parameter is set to true, it will only return true if object is a native int or float.

##### *Parameters*

+ *object*: mixed, the object to pick properties on
+ *native*: bool, optional, if true will not consider SplType instances as numbers

##### *Prototype*

~~~
_::isNumber(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isInteger
-----

_**Alias**_: isInt

_**Description**_: Returns true if the object is an integer. If the optional native parameter is set to true, it will only return true if object is a native int.

##### *Parameters*

+ *object*: mixed, the object
+ *native*: bool, optional, if true will not consider instances of SplInt as integers

##### *Prototype*

~~~
_::isInteger(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isFloat
-----

_**Description**_: Returns true if the object is a float. If the optional native parameter is set to true, it will only return true if object is a native float.

##### *Parameters*

+ *object*: mixed, the object
+ *native*: bool, optional, if true will not consider instances of SplFloat as integers

##### *Prototype*

~~~
_::isFloat(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isString
-----

_**Description**_: Returns true if object is a String. If object is an object with a __toString method, it will be considered as a string as well. If the optionnal native parameter is set to true, it will only return true if object is a native string.

##### *Parameters*

+ *object*: mixed, the object
+ *native*: bool, optional, if true will not consider object with toString or SplString as strings

##### *Prototype*

~~~
_::isString(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isDate
-----

_**Description**_: Returns true if object is a DateTime instance. Everything the strtotime function can understand is also considered a date.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isDate(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isRegExp
-----

_**Description**_: Returns true if object is a valid regular expression (PCRE).

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isRegExp(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isFinite
-----

_**Description**_: Returns true if object is a finite number.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isFinite(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isNaN
-----

_**Description**_: Returns true if object is NaN (Not a Number).

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isNaN(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isBoolean
-----

_**Alias**_: isBool

_**Description**_: Returns true if object is a Boolean. If the optionnal native parameter is set to true, it will only return true if object is a native boolean.

##### *Parameters*

+ *object*: mixed, the object
+ *native*: bool, optional, if true will not consider instances of SplBool as integers

##### *Prototype*

~~~
_::isBoolean(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isNull
-----

_**Description**_: Returns true if object is Null.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isNull(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isScalar
-----

_**Description**_: Returns true if $object is a scalar. If the optionnal native parameter is set to true, it will only return true if object is a native scalar.

##### *Parameters*

+ *object*: mixed, the object
+ *native*: mixed, no description available...

##### *Prototype*

~~~
_::isScalar(object,native)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isTraversable
-----

_**Description**_: Returns true if the object can be traversed with a foreach loop.

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isTraversable(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### isResource
-----

_**Description**_: Returns true if the object is a resource (like a file handle returned by fopen).

##### *Parameters*

+ *object*: mixed, the object

##### *Prototype*

~~~
_::isResource(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

### typeOf
-----

_**Alias**_: getType

_**Description**_: Gets the class of given object or its native type. This function aggregates most of the is* functions and can be seen as a more preceise version of PHP native function gettype. The class parameters lets you know the exact type of the object, if set to false 'object' is returned for objects. Otherwise will return one of the Underscore::TYPE_* constants.

##### *Parameters*

+ *object*: mixed, the object
+ *class*: bool, optional, if true will return the exact class of $object instead of TYPE_OBJECT

##### *Prototype*

~~~
_::typeOf(object,class)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#object-functions">Object Functions</a></p>

## Utility Functions
* [identity](#identity)
* [constant](#constant)
* [noop](#noop)
* [times](#times)
* [random](#random)
* [mixin](#mixin)
* [provide](#provide)
* [uniqueId](#uniqueid)
* [escape](#escape)
* [unescape](#unescape)
* [result](#result)
* [lastly](#lastly)
* [template](#template)

### identity
-----

_**Description**_: Returns the same value that is used as the argument. In math: f(x) = x. This function looks useless, but is used throughout Underscore as a default iterator.

##### *Parameters*

+ *value*: mixed, the value to return

##### *Prototype*

~~~
_::identity(value)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### constant
-----

_**Description**_: Creates a function that returns the same value that is used as the argument of _::constant.

##### *Parameters*

+ *value*: mixed, the value

##### *Prototype*

~~~
_::constant(value)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### noop
-----

_**Description**_: Returns undefined irrespective of the arguments passed to it. Useful as the default for optional callback arguments.

##### *Parameters*


##### *Prototype*

~~~
_::noop()
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### times
-----

_**Description**_: Invokes the given iterator function n times. Each invocation of iterator is called with an index argument. Produces an array of the returned values.

##### *Parameters*

+ *n*: int, the number of time $iterator will be run
+ *iterator*: callable, the iterator function
+ *context*: object, optional, if provided will become the context for $iterator

##### *Prototype*

~~~
_::times(n,iterator,context)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### random
-----

_**Description**_: Returns a random integer between min and max, inclusive. If you only pass one argument, it will return a number between 0 and that number.

##### *Parameters*

+ *min*: int, the lower bound (or the max if $max is null, the min being 0 then)
+ *max*: int, optional the upper bound

##### *Prototype*

~~~
_::random(min,max)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### mixin
-----

_**Description**_: Allows you to extend Underscore with your own utility functions. Pass a hash of array('name' => function) definitions to have your functions added to the Underscore library, as well as the OOP wrapper.

##### *Parameters*

+ *functions*: array, an collection of functions to add to the Underscore class

##### *Prototype*

~~~
_::mixin(functions)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### provide
-----

_**Description**_: Returns callable version of any Underscore method (event the user defined ones).

##### *Parameters*

+ *method*: string, multiple, the Underscore's method name(s)

##### *Prototype*

~~~
_::provide(method)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### uniqueId
-----

_**Description**_: Generate a pseudo-unique id.

##### *Parameters*

+ *prefix*: string, optional, a prefix for the id

##### *Prototype*

~~~
_::uniqueId(prefix)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### escape
-----

_**Description**_: Escapes a string for insertion into HTML, replacing &, <, >, ", ', and / characters.

##### *Parameters*

+ *string*: string, the string to escape

##### *Prototype*

~~~
_::escape(string)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### unescape
-----

_**Description**_: The opposite of escape, replaces &amp;, &lt;, &gt;, &quot;, &#x27;, and &#x2F; with their unescaped counterparts.

##### *Parameters*

+ *string*: string, the string to unescape

##### *Prototype*

~~~
_::unescape(string)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### result
-----

_**Description**_: If the value of the named property is a function then invoke it with the object as context; otherwise, return it.

##### *Parameters*

+ *object*: object,array, the object or array
+ *property*: scalar, the property to get

##### *Prototype*

~~~
_::result(object,property)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### lastly
-----

_**Description**_: The equivalent of the finally keywork (available since PHP 5.5).

##### *Parameters*

+ *function*: callable, a function
+ *finally*: callable,  another function that will *always* be executed after $function
+ *context*: object, the functions context

##### *Prototype*

~~~
_::lastly(function,finally,context)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

### template
-----

_**Description**_: Compiles PHP templates into functions that can be evaluated for rendering. Useful for rendering complicated bits of HTML from JSON data sources. Template functions can both interpolate variables, using <%= ... %>, as well as execute arbitrary PHP code, with <% ... %>. If you wish to interpolate a value, and have it be HTML-escaped, use <%- ... %> When you evaluate a template function, pass in a data object that has properties corresponding to the template's free variables. If you're writing a one-off, you can pass the data object as the second parameter to template in order to render immediately instead of returning a template function. The settings argument should be a hash containing any Underscore::$templateSettings that should be overridden. If ERB-style delimiters aren't your cup of tea, you can change Underscore's template settings to use different symbols to set off interpolated code. Define an interpolate regex to match expressions that should be interpolated verbatim, an escape regex to match expressions that should be inserted after being HTML escaped, and an evaluate regex to match expressions that should be evaluated without insertion into the resulting string. You may define or omit any combination of the three.

##### *Parameters*

+ *templateString*: string, the template buffer
+ *data*: array,object, optional, if provided will compute the template using this array as variables
+ *settings*: array, optional, il provided will override default matchers (use with care)

##### *Prototype*

~~~
_::template(templateString,data,settings)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#utility-functions">Utility Functions</a></p>

## Chaining
* [chain](#chain)

### chain
-----

_**Description**_: Returns a wrapped object. Calling methods on this object will continue to return wrapped objects until value is used. Calling chain will cause all future method calls to return wrapped objects. When you've finished the computation, use value to retrieve the final value.

##### *Parameters*

+ *object*: mixed, the chaining initial state

##### *Prototype*

~~~
_::chain(object)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#chaining">Chaining</a></p>

## Class Forgery
* [forge](#forge)

### forge
-----

_**Alias**_: strategy

_**Description**_: Create new mixins on runtime. The implementation is based on Bob Weinand's idea of Scala traits implementation in PHP (see it here https://gist.github.com/bwoebi/7319798). This method decomposes the $classname to create a new class, using '\with' as a separator for traits.

##### *Parameters*

+ *classname*: string, the class to forge

##### *Prototype*

~~~
_::forge(classname)
~~~

<p align="right"><a href="#table-of-contents">Table of contents</a> &#187; <a href="#class-forgery">Class Forgery</a></p>

