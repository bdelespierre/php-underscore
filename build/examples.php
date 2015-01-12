<?php

each:

_::each([1,2,3], function ($i) { echo $i; });
// => 123

_::each((object)['a'=>1,'b'=>2,'c'=>3], function ($value, $key) { echo "$key => $value\n"; });
// => displays each pair in turn

eachReference:

$numbers = [1,2,3];
_::eachReference($numbers, function (& $value) { $value *= $value; });
// => [1,4,9]

map:

_::map([1,2,3], function ($value) { return $value -1; });
// => [0,1,2]

reduce:

$sum = _::reduce([1,2,3], function ($memo, $num) { return $memo + $num; }, 0);
// => 6

reduceRight:

$list = [[0, 1], [2, 3], [4, 5]];
$flat = _::reduceRight($list, function ($a, $b) { return array_merge($a, $b); }, []);
// => [4,5,2,3,0,1]

find:

$even = _::find([1,2,3,4,5,6], function ($num) { return $num % 2 == 0; });
// => 2

filter:

$evens = _::filter([1,2,3,4,5,6], function ($num) { return $num % 2 == 0; });
// => [2,4,6]

where:

$people = [
    ['name' => 'Jack Nicholson',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Morgan Freeman',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Leonardo Dicaprio', 'born' => 1974, 'profession' => 'actor'],
    ['name' => 'Nathalie Portman',  'born' => 1981, 'profession' => 'actor'],
    ['name' => 'Ridley Scott',      'born' => 1937, 'profession' => 'producer'],
];

$actorsBornIn1937 = _::where($people, ['born' => 1937, 'profession' => 'actor']);
// => Jack Nicholson & Morgan Freeman

findWhere:

$people = [
    ['name' => 'Jack Nicholson',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Morgan Freeman',    'born' => 1937, 'profession' => 'actor'],
    ['name' => 'Leonardo Dicaprio', 'born' => 1974, 'profession' => 'actor'],
    ['name' => 'Nathalie Portman',  'born' => 1981, 'profession' => 'actor'],
    ['name' => 'Ridley Scott',      'born' => 1937, 'profession' => 'producer'],
];

$actor = _::findWhere($people, ['born' => 1937, 'profession' => 'actor']);
// => Jack Nicholsonn

reject:

$odds = _::reject([1,2,3,4,5,6], function ($num) { return $num % 2 == 0; });
// => [1,3,5]

every:

_::every([true, 1, null, 'yes']);
// => false

some:

_::some([null, 0, 'yes', false]);
// => true

contains:

_::contains([1,2,3], 3);
// => true

invoke:

_::invoke([[5, 1, 7], [3, 2, 1]], 'sort');
// => [[1, 5, 7], [1, 2, 3]

pluck:

$stooges = [
    ['name' => 'moe',   'age' => 40],
    ['name' => 'larry', 'age' => 50],
    ['name' => 'curly', 'age' => 60]
];
_::pluck($stooges, 'name');
// => ['moe','larry','curly']

max:

$stooges = [
    ['name' => 'moe',   'age' => 40],
    ['name' => 'larry', 'age' => 50],
    ['name' => 'curly', 'age' => 60]
];
_::max($stooges, function($stooge) { return $stooge['age']; });
// => 60

min:

$numbers = [10, 5, 100, 2, 10000];
_::min($numbers);
// => 2

sortBy:

_::sortBy([1, 2, 3, 4, 5, 6], function($num) { return sin($num); });
// => [5, 4, 6, 3, 1, 2]

groupBy:

_::groupBy([1.3, 2.1, 2.4], function($num) { return floor($num); });
// => [1 => [1.3], 2 => [2.1, 2.4]]

$values = [
    ['val' => 'one',   'length' => 3],
    ['val' => 'two',   'length' => 3],
    ['val' => 'three', 'length' => 5]
];
_::groupBy($values, 'length');
// => [3 => [['val' => 'one', 'lenght' => 3], ['val' => 'two', 'length' => 3], 5 => [['val' => 'three', 'length' => 5]]]

indexBy:

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

countBy:

_::countBY([1, 2, 3, 4, 5], function($num) {
    return $num % 2 == 0 ? 'even' : 'odd';
});
// => ['odd' => 3, 'even' => 2]

shuffle:

_::shuffle([1, 2, 3, 4, 5, 6]);
// => [4, 1, 6, 3, 5, 2]

sample:

_::sample([1, 2, 3, 4, 5, 6]);
// => 4

_::sample([1, 2, 3, 4, 5, 6], 3);
// => [1, 6, 2]

toArray:

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

size:

$object = new stdClass;
$object->one   = 1;
$object->two   = 2;
$object->three = 3;
_::size($object);
// => 3

partition:

_::partition([0, 1, 2, 3, 4, 5], function($num) { return $num % 2 != 0; });
// => [[1, 3, 5], [0, 2, 4]]

