<?php

namespace Underscore\tests\units;

require_once __DIR__ . '/../../src/Underscore/Underscore.php';
require_once __DIR__ . '/../../src/Underscore/Bridge.php';
require_once __DIR__ . '/../../tests/mocks/Doer.php';
require_once __DIR__ . '/../../tests/mocks/ClosureClass.php';
require_once __DIR__ . '/../../tests/mocks/SomeClass.php';
require_once __DIR__ . '/../../tests/mocks/MagicMethods.php';

class_exists('\SplType', false) ||
require_once __DIR__ . '/../../tests/mocks/SplType.php';

use \atoum;
use \Underscore\Underscore as _;

/**
 * NOTE: for integrity's sake, please keep the test methods in the same order as Underscore class methods.
 */

class Underscore extends atoum
{
	/**
	 * Collection Functions
	 * --------------------
	 */

	/**
	 * @tags collections
	 */
	public function testEach()
	{
		// it should provide the value, key and list to the iterator (in that order)
		$test = $this;
		_::each(['a' => 1, 'b' => 2, 'c' => 3], function($v,$k,$l) use ($test) {
			$test->integer($v);
			$test->string($k);
			$test->array($l);
		});

		// it should work with arrays, objects and iterators
		$this
			->typeTolerant(['a' => 1, 'b' => 2, 'c' => 3], 'a1b2c3', function($in, $out) {
				$this
					->output(function() use ($in) {
						_::each($in, function($v,$k) { print $k.$v; });
					})
					->isEqualTo($out);
			}, [0,-1]);

		// it should be possible to specify a context for the iterator function
		$this
			->output(function() {
				_::each(['a' => 1, 'b' => 2, 'c' => 3], function($v,$k) {
					print $k.($v * $this->mult);
				}, (object)['mult' => 2]);
			})
			->isEqualTo('a2b4c6');

		// it should be possible to stop iteration by returning _::BREAKER
		$this
			->output(function() {
				_::each(['a' => 1, 'b' => 2, 'c' => 3], function($v,$k) {
					print $k.$v;

					if ($k == 'b')
						return _::BREAKER;
				});
			})
			->isEqualTo('a1b2');

		// it should throw an InvalidArgumentException when the list provided is not iterable
		$this
			->exception(function() {
				_::each("hello", function() {});
			})
			->isInstanceOf('\InvalidArgumentException');

		// it should not allow to modify list items
		_::each($items = ['a' => 1, 'b' => 2, 'c' => 3], function(& $v,$k) { $v *= 2; });
		$this
			->variable($items)
			->isIdenticalTo(['a' => 1, 'b' => 2, 'c' => 3]);

		// it should return the list for convenience
		$items = _::each(['a' => 1, 'b' => 2, 'c' => 3], function() { return _::BREAKER; });
		$this
			->variable($items)
			->isIdenticalTo(['a' => 1, 'b' => 2, 'c' => 3]);
	}

	/**
	 * @tags collections
	 */
	public function testEachReference()
	{
		// it should provide the value, key and list to the iterator (in that order)
		$test  = $this;
		$items = ['a' => 1, 'b' => 2, 'c' => 3];
		_::eachReference($items, function($v,$k,$l) use ($test) {
			$test->integer($v);
			$test->string($k);
			$test->array($l);
		});

		// it should allow modification of list items (being array or objects)
		$this
			->typeTolerant(['a' => 1, 'b' => 2, 'c' => 3], ['a' => 2, 'b' => 4, 'c' => 6], function($in, $out) {
				if ($in instanceOf \Iterator)
					return;

				_::eachReference($in, function(& $v) { $v *= 2; });
				$this
					->variable($in)
					->isEqualTo($out);
			});

		// it should be possible to specify a context for iterator function
		$items = ['a' => 1, 'b' => 2, 'c' => 3];
		_::eachReference($items, function(& $v) {
			$v *= $this->mult;
		}, (object)['mult' => 2]);
		$this
			->variable($items)
			->isEqualTo(['a' => 2, 'b' => 4, 'c' => 6]);

		// it should be possible to stop iteration by returning _::BREAKER
		$items = ['a' => 1, 'b' => 2, 'c' => 3];
		_::eachReference($items, function(& $v, $k) {
			$v *= 2;
			if ($k == 'b')
				return _::BREAKER;
		});
		$this
			->variable($items)
			->isEqualTo(['a' => 2, 'b' => 4, 'c' => 3]);

		// it should throw an UnexpectedValueException when used with an iterator
		$this
			->exception(function() {
				$items = new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
				_::eachReference($items, function() {});
			})
			->isInstanceOf('\UnexpectedValueException');

		// it should throw an InvalidArgumentException when the list provided is not iterable
		$this
			->exception(function() {
				$items = "hello";
				_::eachReference($items, function() {});
			})
			->isInstanceOf('\InvalidArgumentException');

		// it should return the list for convenience
		$items = ['a' => 1, 'b' => 2, 'c' => 3];
		$this
			->variable(_::eachReference($items, function() { return _::BREAKER; }))
			->isIdenticalTo($items);
	}

	/**
	 * @tags collections
	 */
	public function testMap()
	{
		// it should provide the value, key and list to the iterator (in that order)
		$test = $this;
		_::map(['a' => 1, 'b' => 2, 'c' => 3], function($v,$k,$l) use ($test) {
			$test->integer($v);
			$test->string($k);
			$test->array($l);
		});

		// it should work with array, objects and iterators and return an array in every case
		$this
			->typeTolerant([1,2,3], [2,4,6], function($in, $out) {
				$this
					->array(_::map($in, function($v) { return $v *= 2; }))
					->isEqualTo($out);
			}, [0,-1]);

		// it should be possible to specify a context for iterator function
		$this
			->variable(_::map([1,2,3], function($v) { return $v * $this->mult; }, (object)['mult' => 2]))
			->isEqualTo([2,4,6]);
	}

	/**
	 * @tags collections
	 */
	public function testReduce()
	{
		// it should provide the memo, the value, the index and the list to the reduction function (in that order)
		$test = $this;
		_::reduce(['a' => 1, 'b' => 2, 'c' => 3], function($m,$v,$k,$l) use ($test) {
			$test->integer($v);
			$test->string($k);
			$test->array($l);
			$test->boolean(is_null($m))->isTrue();
			return $m;
		}, null);

		$this
			->typeTolerant([1,2,3], 6, function($in, $out) {
				$this
					->variable(_::reduce($in, function($m, $v) { return $m + $v; }, 0))
					->isEqualTo($out);
			}, [0,-1]);

		// it should be possible to specify a context for reduction function
		$this
			->variable(_::reduce([1,2,3], function($m,$v) {
				return ($m + $v) * $this->mult;
			}, 0, (object)['mult' => 2]))
			->isEqualTo(22);
	}

	/**
	 * @tags collections
	 */
	public function testReduceRight()
	{
		// it should provide the memo, the value, the index and the list to the reduction function (in that order)
		$test = $this;
		_::reduceRight(['a' => 1, 'b' => 2, 'c' => 3], function($m,$v,$k,$l) use ($test) {
			$test->integer($v);
			$test->string($k);
			$test->array($l);
			$test->boolean(is_null($m))->isTrue();
			return $m;
		}, null);

		$this
			->typeTolerant([[1,2],[3,4],[5,6]], [5,6,3,4,1,2], function($in, $out) {
				$this
					->variable(_::reduceRight($in, function($m, $v) { return array_merge($m, $v); }, []))
					->isEqualTo($out);
			}, [0,-1]);

		// it should be possible to specify a context for reduction function
		$this
			->variable(_::reduceRight([[1,2],[3,4],[5,6]], function($m,$v) {
				$v[0] *= $this->mult;
				$v[1] *= $this->mult;
				return array_merge($m, $v);
			}, [], (object)['mult' => 2]))
			->isEqualTo([10,12,6,8,2,4]);
	}

	/**
	 * @tags collections
	 */
	public function testFind()
	{
		$even = function($v) { return !($v & 1); };

		// it should return the first value that match the truth test and work with arrays, objects and iterators
		$this
			->typeTolerant([1,2,3,4], 2, function($in, $out) use ($even) {
				$this
					->integer(_::find($in, $even))
					->isEqualTo($out);
			}, [0,-1]);

		// it should return null when no value match the truth test
		$this
			->variable(_::find([1,3,5], $even))
			->isNull();

		// it should be possible to specify a context for the thruth test
		$this
			->variable(_::find([1,2,3,4], function($v) { return $v % $this->mod == 0; }, (object)['mod' => 2]))
			->isEqualTo(2);

		// it should stop iterating over the list once an element is found
		$i = 0;
		_::find([1,2,3,4], function($v) use (& $i) { $i++; return !($v & 1); });
		$this
			->variable($i)
			->isEqualTo(2);
	}

	/**
	 * @tags collections
	 */
	public function testFilter()
	{
		$even = function($v) { return !($v & 1); };

		// the truth test should be optionnal
		$this
			->variable(_::filter([1,0,2,null,3,false,4,"0"]))
			->isEqualTo([0 =>1, 2 => 2, 4 => 3, 6 => 4]);

		// it should work with arrays, objects and iterators
		$this
			->typeTolerant([1,2,3,4], [1 => 2, 3 => 4], function($in, $out) use ($even) {
				$this
					->array(_::filter($in, $even))
					->isEqualTo($out);
			}, [0, -1]);

		// it should preserve associativity and order
		$this
			->variable(_::filter(['a' => 1, 9 => 2, null => 3, .2 => 4], $even))
			->isEqualTo([9 => 2, .2 => 4]);

		// it should be possible to specify a context for the thruth test
		$this
			->variable(_::filter([1,2,3,4], function($v) { return $v % $this->mod == 0; }, (object)['mod' => 2]))
			->isEqualTo([1 => 2, 3 => 4]);

		// it should return an empty array if no item passes the truth test
		$this
			->array(_::filter([1,3,5,7], $even))
			->hasSize(0);
	}

	/**
	 * @dataProvider peopleDataprovider
	 * @tags collections
	 */
	public function testWhere($people, $type, $meta)
	{
		$search = ['born' => 1937, 'profession' => 'actor'];

		// it should return the rows that contains all the properties/values
		$this
			->array(_::where($people, static::{"to$type"}($search)))
			->contains(_::get($people, 'jnicholson'))
			->contains(_::get($people, 'mfreeman'))
			->hasKeys(['jnicholson','mfreeman']);

		// it should return an empty array if no matches are found
		$this
			->array(_::where($people, ['born' => 2002]))
			->hasSize(0);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testFindWhere($people, $type, $meta)
	{

		$search = ['born' => 1937, 'profession' => 'actor'];

		// it should return the first row that contains all the properties/values
		$this
			->variable(_::findWhere($people, static::{"to$type"}($search)))
			->isEqualTo(_::get($people, 'jnicholson'));

		// it should return null if no match is found
		$this
			->variable(_::findWhere($people, ['born' => 2002]))
			->isNull();
	}

	/**
	 * @tags collections
	 */
	public function testReject()
	{
		$even = function($v) { return !($v & 1); };

		// it should work with arrays, objects and iterators
		$this
			->typeTolerant([1,2,3,4], [0 => 1, 2 => 3], function($in, $out) use ($even) {
				$this
					->array(_::reject($in, $even))
					->isEqualTo($out);
			}, [0,-1]);

		// it should preserve associativity and order
		$this
			->variable(_::reject(['a' => 1, 9 => 2, null => 3, .2 => 4], $even))
			->isEqualTo(['a' => 1, null => 3]);

		// it should be possible to specify a context for the thruth test
		$this
			->variable(_::reject([1,2,3,4], function($v) { return $v % $this->mod == 0; }, (object)['mod' => 2]))
			->isEqualTo([0 => 1, 2 => 3]);

		// it should return an empty array if everything passes the truth test
		$this
			->array(_::reject([2,4,6,8], $even))
			->hasSize(0);

		// it should return an empty array if the list is empty
		$this
			->array(_::reject([], $even))
			->hasSize(0);
	}

	/**
	 * @tags collections
	 */
	public function testEvery()
	{
		$even = function($v) { return !($v & 1); };

		// it should work with arrays, objects and iterators
		$this
			->typeTolerant([2,4,6,8], null, function($in, $out) use ($even) {
				$this
					->boolean(_::every($in, $even))
					->isTrue();
			}, [0,-1]);

		// the truth test should be optionnal
		$this
			->boolean(_::every([1,true,"1",[null],(object)[]]))
			->isTrue();

		// it should return true if all items in the list passes the truth test
		$this
			->boolean(_::every([2,4,6,8], $even))
			->isTrue();

		// it should return false if at leat one item in the list doesn't pass the truth test
		$this
			->boolean(_::every([2,4,5,6], $even))
			->isFalse();

		// it should stop the list iteration when an item doesn't pass the truth test
		$i = 0;
		_::every([2,4,5,6], function($v) use (& $i, $even) { $i++; return $even($v); });
		$this
			->variable($i)
			->isEqualTo(3);

		// it should be possible to specify a context for the truth test
		$this
			->boolean(_::every([2,4,6,8], function($v) { return $v % $this->mod == 0; }, (object)['mod' => 2]))
			->isTrue();

		// it should return true if the list is empty
		$this
			->boolean(_::every([], function($v) { return true; }))
			->isTrue();
	}

	/**
	 * @tags collections
	 */
	public function testSome()
	{
		$even = function($v) { return !($v & 1); };

		// it should work with arrays, objects and iterators
		$this
			->typeTolerant([1,3,4,5], null, function($in, $out) use ($even) {
				$this
					->boolean(_::some($in, $even))
					->isTrue();
			}, [0,-1]);

		// the truth test should be optionnal
		$this
			->boolean(_::some([false, 0, "0", null, true, 2]))
			->isTrue();

		// it should return true if at leat one item in the list pass the truth test
		$this
			->boolean(_::some([1,3,4,5], $even))
			->isTrue();

		// it should return false if all items in the list doesn't pass the truth test
		$this
			->boolean(_::some([1,3,5,7], $even))
			->isFalse();

		// it should stop the list iteration when an item pass the truth test
		$i = 0;
		_::some([1,3,4,5], function($v) use (& $i, $even) { $i++; return $even($v); });
		$this
			->variable($i)
			->isEqualTo(3);

		// it should be possible to specify a context for the truth test
		$this
			->boolean(_::some([1,3,4,5], function($v) { return $v % $this->mod == 0; }, (object)['mod' => 2]))
			->isTrue();

		// it should return false if the list is empty
		$this
			->boolean(_::some([], function($v) { return true; }))
			->isFalse();
	}

	/**
	 * @tags collections
	 */
	public function testContains()
	{
		// it should work with array, objects and iterators
		$this
			->typeTolerant([1,2,3], 2, function($in, $out) {
				$this
					->boolean(_::contains($in, $out))
					->isTrue();
			}, [0,-1]);

		// it should return false if the item is not present in the list
		$this
			->boolean(_::contains([1,2,3], 4))
			->isFalse();

		// it should be capable of strict equality
		$this
			->boolean(_::contains([1,2,3], "2", true))
			->isFalse();

		// it should return false if the list is empty
		$this
			->boolean(_::contains([], 2))
			->isFalse();
	}

	/**
	 * @tags collections
	 */
	public function testInvoke()
	{
		// it should invoke a given method for every list object
		$doers = \Doer::create(3, function() { $this->invoked = true; });
		$this
			->typeTolerant($doers, 'doYourJob', function($in, $out) {
				$this
					->array(_::invoke($in, $out))
					->hasSize(3);

				foreach($in as $obj)
					$this
						->boolean($obj->invoked)
						->isTrue();
			}, [0,-1]);

		// it should invoke the ArrayObject's method on native arrays
		$this
			->array(_::invoke([[2,3,1],[6,4,5],[8,7,9]], 'asort'))
			->isEqualTo([
				[2 => 1, 0 => 2, 1 => 3],
				[1 => 4, 2 => 5, 0 => 6],
				[1 => 7, 0 => 8, 2 => 9]
			]);

		// it should be capable of applying a closure on every item
		$doers = \Doer::create(3, function() {});
		_::invoke($doers, function() { $this->invoked = true; });
		foreach ($doers as $doer)
			$this
				->boolean($doer->invoked)
				->isTrue();

		// it should be capable to accept arguments (as array or variadics)
		$doers = \Doer::create(3, function($a,$b,$c) { $this->v = $a+$b+$c; });
		_::invoke($doers, 'doYourJob', [1,2,3]);
		foreach ($doers as $doer)
			$this
				->integer($doer->v)
				->isEqualTo(6);

		$doers = \Doer::create(3, function($a,$b,$c) { $this->v = $a+$b+$c; });
		_::invoke($doers, 'doYourJob', 1, 2, 3);
		foreach ($doers as $doer)
			$this
				->integer($doer->v)
				->isEqualTo(6);

		// it should ignore non-objects without producing errors
		$this
			->when(function() use (& $i) {
				$i = 0;
				$fn = function() use (& $i) { $i++; };
				$doers = [new \Doer($fn), 1, new \Doer($fn), 2];
				_::invoke($doers, 'doYourJob');
			})
			->error()
				->notExists();
		$this
			->integer($i)
			->isEqualTo(2);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testPluck($people, $type, $meta)
	{
		// it should extract a single column from a 2 dimentionnal array
		$this
			->array(_::pluck($people, 'name'))
			->containsValues($meta['names'])
			->hasSize($meta['count'])
			->hasKeys($meta['keys']);

		// it should set values to null when given property is not found
		$this
			->array(_::pluck($people, 'inexistent'))
			->isEqualTo(array_combine($meta['keys'], array_fill(0, $meta['count'], null)));
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testMax($people, $type, $meta)
	{
		$age = function ($v) { return 2014 - _::get($v, 'born'); };

		// it should return the first max element
		$this
			->variable(_::max($people, $age))
			->isEqualTo(_::get($people, 'jnicholson'));

		// the iterator function should be optional
		$this
			->typeTolerant([1,4,2,3], 4, function($in, $out) {
				$this
					->integer(_::max($in))
					->isEqualTo($out);
			}, [0,-1]);

		// it should be possible to specify a context for iteration function
		$age = function($v) { return $this->year - _::get($v, 'born'); };
		$context = (object)['year' => 2014];
		$this
			->variable(_::max($people, $age, $context))
			->isEqualTo(_::get($people, 'jnicholson'));
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testMin($people, $type, $meta)
	{
		$age = function($v) { return 2014 - _::get($v, 'born'); };

		// it should return the first min element
		$this
			->variable(_::min($people, $age))
			->isEqualTo(_::get($people, 'nportman'));

		// the iterator function should be optional
		$this
			->typeTolerant([1,4,2,3], 1, function($in, $out) {
				$this
					->integer(_::min($in))
					->isEqualTo($out);
			}, [0,-1]);

		// it should be possible to specify a context for iteration function
		$age = function($v) { return $this->year - _::get($v, 'born'); };
		$context = (object)['year' => 2014];
		$this
			->variable(_::min($people, $age, $context))
			->isEqualTo(_::get($people, 'nportman'));
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testSortBy($people, $type, $meta)
	{
		$age = function($v) { return 2014 - _::get($v, 'born'); };
		$keys = ['nportman', 'ldicaprio', 'rscott', 'mfreeman', 'jnicholson'];

		// it sorts the list items using criteria calculation function
		$this
			->array(_::sortBy($people, $age))
			->keys
				->isEqualTo($keys);

		// it should be possible to specify a context for criteria calculcation function
		$age = function($v) { return $this->year - _::get($v, 'born'); };
		$context = (object)['year' => 2014];
		$this
			->array(_::sortBy($people, $age, $context))
			->keys
				->isEqualTo($keys);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testIndexBy($people, $type, $meta)
	{
		$profession = function($v) { return _::get($v, 'profession'); };
		$indexedPeople = [
			'actor'    => _::get($people, 'nportman'),
			'producer' => _::get($people, 'rscott'),
		];

		// it sorts the list items using criteria calculation function
		$this
			->array(_::indexBy($people, $profession))
			->isEqualTo($indexedPeople);

		// it should accept a property name instead of a closure
		$this
			->array(_::indexBy($people, 'profession'))
			->isEqualTo($indexedPeople);

		// it should be possible to specify a context for criteria function
		$profession = function($v) { return _::get($v, $this->property); };
		$context = (object)['property' => 'profession'];
		$this
			->array(_::indexBy($people, $profession, $context))
			->isEqualTo($indexedPeople);

		// it should works with an inexistent property name by put all results under NULL index
		$this
			->array(_::indexBy($people, 'inexistantProperty'))
			->keys
				->isEqualTo([null]);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testGroupBy($people, $type, $meta)
	{
		$profession = function($v) { return _::get($v, 'profession'); };
		$groupedPeople = [
			'actor' => [
				_::get($people, 'jnicholson'),
				_::get($people, 'mfreeman'),
				_::get($people, 'ldicaprio'),
				_::get($people, 'nportman'),
			],
			'producer' => [
				_::get($people, 'rscott'),
			],
		];

		// it should group items into 2 dimentionnals arrays using a criteria function
		$this
			->array(_::groupBy($people, $profession))
			->isEqualTo($groupedPeople);

		// it should accept a property name instead of a closure
		$this
			->array(_::groupBy($people, 'profession'))
			->isEqualTo($groupedPeople);

		// it should be possible to specify a context for criteria function
		$profession = function($v) { return _::get($v, $this->property); };
		$context = (object)['property' => 'profession'];
		$this
			->array(_::groupBy($people, $profession, $context))
			->isEqualTo($groupedPeople);

		// it should works with an inexistent property name by put all results under NULL index
		$this
			->array(_::groupBy($people, 'inexistantProperty'))
			->keys
				->isEqualTo([null]);

		// it should not preserve original list keys
		$this
			->array(_::groupBy(['a' => ['v' => 1], 'b' => ['v' => 2], 'c' => ['v' => 1]], 'v'))
			->isEqualTo([1 => [['v' => 1],['v' => 1]],2 => [['v' => 2]]]);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testCountBy($people, $type, $meta)
	{
		$profession = function($v) { return _::get($v, 'profession'); };
		$countedPeople = ['actor' => 4, 'producer' => 1];

		// it should count items by a criteria function
		$this
			->array(_::countBy($people, $profession))
			->isEqualTo($countedPeople);

		// it should accept a property name instead of a closure
		$this
			->array(_::countBy($people, 'profession'))
			->isEqualTo($countedPeople);

		// it should be possible to specify a context
		$profession = function($v) { return _::get($v, $this->property); };
		$context = (object)['property' => 'profession'];
		$this
			->array(_::countBy($people, $profession, $context))
			->isEqualTo($countedPeople);

		// it should put all results under a null key if property doesn't exists
		$this
			->array(_::countBy($people, 'inexistantProperty'))
			->keys
				->isEqualTo([null]);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testShuffle($people, $type, $meta)
	{
		$values = range(1,9999);

		// it shuffles a list (and always returns an array)
		$this
			->array(_::shuffle($values))
			->isNotEqualTo($values)
			->hasSize(9999);

		// it can shuffle anything
		$this
			->array(_::shuffle($people))
			->keys
				->containsValues($meta['keys'])
				->isNotEqualTo($meta['keys']);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testSample($people, $type, $meta)
	{
		$values = [1,2,3,4,5];

		// it should select 1 item from a list
		$this
			->array($values)
			->contains(_::sample($values))
			->contains(_::sample($values))
			->contains(_::sample($values))
			->contains(_::sample($values));

		// it can select multiple items at a time
		$this
			->array($values)
			->containsValues(_::sample($values, 3));

		// it can sample anything
		$values = _::toArray($people);
		$this
			->array($values)
			->containsValues(_::sample($people, 3));
	}

	/**
	 * @tags collections
	 */
	public function testToArray()
	{
		// it should be able to cast scalars
		$this
			->array(_::toArray("hello"))
			->isEqualTo(["hello"]);

		// it should return an empty array for null
		$this
			->array(_::toArray(null))
			->isEqualTo([]);

		// it should pack arguments in an array
		$this
			->array(_::toArray(1,2,3))
			->isEqualTo([1,2,3]);

		// it should convert ArrayObjects into array
		$this
			->array(_::toArray(new \ArrayObject([1,2,3])))
			->isEqualTo([1,2,3]);

		// it should convert traversables into array
		$this
			->array(_::toArray(new \IteratorIterator(new \ArrayIterator([1,2,3]))))
			->isEqualTo([1,2,3]);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testSize($people, $type, $meta)
	{
		// it should count the number of items in any list
		$this
			->integer(_::size($people))
			->isEqualTo(5);

		// it should be able to count scalar (even if it mean nothing...)
		$this
			->integer(_::size("hello"))
			->isEqualTo(1);

		// it should return 0 for null
		$this
			->integer(_::size(null))
			->isIdenticalTo(0);
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags collections
	 */
	public function testPartition($people, $type, $meta)
	{
		$isActor = function($v) { return _::get($v, 'profession') == 'actor'; };

		// it should separate items according to a distinction function
		$partitions = _::partition($people, $isActor);
		$this
			->integer(count($partitions[0]))
			->isEqualTo(4);
		$this
			->integer(count($partitions[1]))
			->isEqualTo(1);

		// it should be possible to pass a context for the distinction function
		$is = function($v) { return _::get($v, 'profession') == $this->profession; };
		$context = (object)['profession' => 'actor'];
		$partitions = _::partition($people, $is, $context);
		$this
			->integer(count($partitions[0]))
			->isEqualTo(4);
		$this
			->integer(count($partitions[1]))
			->isEqualTo(1);
	}

	/**
     * Array Functions
     * ---------------
     */

	/**
	 * @dataProvider peopleDataProvider
	 * @tags arrays
	 */
	public function testFirst($people, $type, $meta)
	{
		// it should return the first list item
		$this
			->variable(_::first($people))
			->isEqualTo(_::get($people, 'jnicholson'));

		// it should return the first N list items
		$this
			->array(_::first($people, 3))
			->isEqualTo([
				_::get($people, 'jnicholson'),
				_::get($people, 'mfreeman'),
				_::get($people, 'ldicaprio')
			]);

		// it should always return an array when guard is true
		$this
			->array(_::first($people, 1, true))
			->isEqualTo([_::get($people, 'jnicholson')]);

		// it should throw an exception if the number of items to retrieve is invalid
		$this
			->exception(function() use ($people) {
				_::first($people, -1);
			})
			->isInstanceOf('\UnexpectedValueException');

		// it should return null if provided an empty list
		$this
			->variable(_::first([]))
			->isNull();
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags arrays
	 */
	public function testInitial($people, $type, $meta)
	{
		// it should return all item in list but the last
		$this
			->array(_::initial($people))
			->isEqualTo([
				_::get($people, 'jnicholson'),
				_::get($people, 'mfreeman'),
				_::get($people, 'ldicaprio'),
				_::get($people, 'nportman'),
			]);

		// it should return the first items in the list but the N last
		$this
			->array(_::initial($people, 3))
			->isEqualTo([
				_::get($people, 'jnicholson'),
				_::get($people, 'mfreeman'),
			]);

		// if only one item is picked, it should be directly returned
		$this
			->variable(_::initial($people, 4))
			->isEqualTo(_::get($people, 'jnicholson'));

		// it should be capable the initial item directly (guarding its type)
		$this
			->variable(_::initial($people, 4, true))
			->isEqualTo([_::get($people, 'jnicholson')]);

		// it should return null if provided an empty list
		$this
			->variable(_::initial([]))
			->isNull();
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags arrays
	 */
	public function testLast($people, $type, $meta)
	{
		// it should return the last list item
		$this
			->variable(_::last($people))
			->isEqualTo(_::get($people, 'rscott'));

		// it should return the last N list items
		$this
			->array(_::last($people, 3))
			->isEqualTo([
				_::get($people, 'ldicaprio'),
				_::get($people, 'nportman'),
				_::get($people, 'rscott')
			]);

		// it should always return an array when guard is true
		$this
			->array(_::last($people, 1, true))
			->isEqualTo([_::get($people, 'rscott')]);

		// it should return null if provided an empty list
		$this
			->variable(_::last([]))
			->isNull();
	}

	/**
	 * @dataProvider peopleDataProvider
	 * @tags arrays
	 */
	public function testRest($people, $type, $meta)
	{
		// it should return the rest list item
		$this
			->variable(_::rest($people, 4))
			->isEqualTo(_::get($people, 'rscott'));

		// it should return the rest N list items
		$this
			->array(_::rest($people, 3))
			->isEqualTo([
				_::get($people, 'nportman'),
				_::get($people, 'rscott')
			]);

		// it should always return an array when guard is true
		$this
			->array(_::rest($people, 4, true))
			->isEqualTo([_::get($people, 'rscott')]);

		// it should return null if provided an empty list
		$this
			->variable(_::rest([]))
			->isNull();
	}

	/**
	 * @tags arrays
	 */
	public function testCompact()
	{
		// it should remove all "falsy" entries
		$this
			->array(_::compact([false, 0, null, "", [], "0"]))
			->hasSize(0);

		// it should keey the "truthy" entries
		$this
			->array(_::compact([false, true, false]))
			->hasSize(1);

		$this
			->array(_::compact([1,2,3]))
			->hasSize(3);

		$this
			->array(_::compact([]))
			->hasSize(0);

		// it should work with array objects an iterators
		$this
			->typeTolerant([0,1,2,3,false,4,"0"], 4, function($in, $out) {
				$this
					->array(_::compact($in))
					->hasSize($out);
			}, [1,-1]);

		// it should return an empty array if list is empty
		$this
			->array(_::compact(null))
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testFlatten()
	{
		// it should flatten arrays
		$this
			->array(_::flatten([[1],[2],[3]]))
			->isEqualTo([1,2,3]);

		$this
			->array(_::flatten([[1,2],[3,4]]))
			->isEqualTo([1,2,3,4]);

		// it should deep-flatten arrays by default
		$this
			->array(_::flatten([[[1]],[[2]],[[3]]]))
			->isEqualTo([1,2,3]);

		// it should be possible to obtain a shallow copy
		$this
			->array(_::flatten([[[1]],[[2]],[[3]]], true))
			->isEqualTo([[1],[2],[3]]);

		// it should work on arrays objects an iterators
		$this
			->typeTolerant([[1,2],[3,[4]]], [1,2,3,4], function($in, $out) {
				$this
					->array(_::flatten($in))
					->isEqualTo($out);
			}, [1, -1]);

		// it should return an empty array if list is empty
		$this
			->array(_::flatten(null))
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testWithout()
	{
		// it should return a copy of the array with all instances of the values removed
		$this
			->typeTolerant([1,2,3], [1], function($in, $out) {
				$this
					->array(_::without($in, 2, 3))
					->isEqualTo($out);
			}, [0,-1]);

		// it should return an empty array if list is empty
		$this
			->array(_::without(null, 1))
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testUniq()
	{
		// it should produce a duplicate-free version of the array
		$this
			->typeTolerant(['a'=>1, 'b'=>2, 'c'=>3, 'd'=>3], [1,2,3], function($in, $out) {
				$this
					->array(_::uniq($in))
					->isEqualTo($out);
			}, [0,-1]);

		// it should return an empty array if list is empty
		$this
			->array(_::uniq(null))
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testUnion()
	{
		// it should compute the union of the passed-in arrays
		$values = [
			['a' => 1, 'b' => 2],
			['c' => 2, 'd' => 3],
			['e' => 3, 'f' => 4],
		];
		$this
			->typeTolerant($values, [1,2,3,4], function($in, $out) {
				$in = _::toArray($in);
				$this
					->array(_::union($in[0], $in[1], $in[2]))
					->isEqualTo($out);
			}, [1,-1]);

		// it should be capable to compute unions of different type
		$this
			->array(_::union($values[0], self::toObject($values[1]), self::toIterator($values[2])))
			->isEqualTo([1,2,3,4]);
	}

	/**
	 * @tags arrays
	 */
	public function testIntersection()
	{
		// it should compute the list of values that are the intersection of all the arrays
		$values = [
			['a' => 1, 'b' => 2, 'c' => 3],
			['a' => 4, 'b' => 1, 'c' => 2],
			['a' => 0, 'b' => 3, 'c' => 1],
		];
		$this
			->typeTolerant($values, [1], function($in, $out) {
				$in = _::toArray($in);
				$this
					->array(_::intersection($in[0], $in[1], $in[2]))
					->isEqualTo($out);
			}, [1,-1]);

		// it should be capable to compute intersection of different type
		$this
			->array(_::intersection($values[0], self::toObject($values[1]), self::toIterator($values[2])))
			->isEqualTo([1]);

		// it should return the array itself if it's the only argument
		$this
			->array(_::intersection($values[0]))
			->isEqualTo($values[0]);

		// it should return an empty array if the list is empty
		$this
			->array(_::intersection(null))
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testDifference()
	{
		// it should return the values from array that are not present in the other arrays
		$values = [
			['a' => 1, 'b' => 2, 'c' => 3],
			['a' => 4, 'b' => 2, 'c' => 6],
			['a' => 7, 'b' => 8, 'c' => 3],
		];
		$this
			->typeTolerant($values, [1], function($in, $out) {
				$in = _::toArray($in);
				$this
					->array(_::difference($in[0], $in[1], $in[2]))
					->isEqualTo($out);
			}, [0, -1]);

		// it should be capable to compute difference of different types
		$this
			->array(_::difference($values[0], self::toObject($values[1]), self::toIterator($values[2])))
			->isEqualTo([1]);

		// it should return the array itself if it's the only argument
		$this
			->array(_::difference($values[0]))
			->isEqualTo($values[0]);

		// it should return an empty array if the list is empty
		$this
			->array(_::difference(null))
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testZip()
	{
		// it should merge together the values of each of the arrays with the values at the corresponding position
		$values = [
			['moe', 'larry', 'curly'],
			[30,    40,      50     ],
			[true,  false,   false  ]
		];
		$this
			->typeTolerant($values, null, function($in, $out) {
				$in = _::toArray($in);
				$this
					->array(_::zip($in[0], $in[1], $in[2]))
					->isEqualTo([
						['moe',   30, true ],
						['larry', 40, false],
						['curly', 50, false]
					]);
			}, [0, -1]);

		// it should return an empty array if the list of arrays to zip is empty
		$this
			->array(_::zip())
			->isEqualTo([]);
	}

	/**
	 * @tags arrays
	 */
	public function testObj()
	{
		// it should convert arrays into objects
		$this
			->object(_::obj(['a','b','c'], [1,2,3]))
			->isInstanceOf('\stdClass')
			->isEqualTo((object)['a' => 1, 'b' => 2, 'c' => 3]);

		// it should be capable to create empty objects
		$this
			->object(_::obj(['a','b','c']))
			->isInstanceOf('\stdClass')
			->isEqualTo((object)['a' => null, 'b' => null, 'c' => null]);

		// if arrays are empty, return an empty object
		$this
			->object(_::obj([]))
			->isInstanceOf('\stdClass')
			->isEqualTo(new \stdClass);
	}

	/**
	 * @tags arrays
	 */
	public function testIndexOf()
	{
		$values = [1,2,3,2,1];

		// it should return the position of given element
		$this
			->typeTolerant($values, null, function($in, $out) {
				$this
					->variable(_::indexOf($in, 2))
					->isEqualTo(1);
			}, [0, -1]);

		// it should return -1 if the element is not in the list
		$this
			->typeTolerant($values, null, function($in, $out) {
				$this
					->variable(_::indexOf($in, 5))
					->isEqualTo(-1);
			}, [0, -1]);

		// it should return the key instead of offset when used with an hashmap
		$values = [
			'a' => 1,
			'b' => 2,
			'c' => 3,
		];
		$this
			->typeTolerant($values, null, function($in, $out) {
				$this
					->variable(_::indexOf($in, 2))
					->isEqualTo('b');
			}, [0, -1]);
	}

	/**
	 * @tags arrays
	 */
	public function testLastIndexOf()
	{
		$values = [1,2,3,2,1];

		// it should return the last position of given element
		$this
			->typeTolerant($values, null, function($in, $out) {
				$this
					->variable(_::lastIndexOf($in, 2))
					->isEqualTo(3);
			}, [0, -1]);

		// it should return -1 if the element is not in the list
		$this
			->typeTolerant($values, null, function($in, $out) {
				$this
					->variable(_::lastIndexOf($in, 5))
					->isEqualTo(-1);
			}, [0, -1]);

		// it should return the key instead of offset when used with an hashmap
		$values = [
			'a' => 1,
			'b' => 2,
			'c' => 3,
		];
		$this
			->typeTolerant($values, null, function($in, $out) {
				$this
					->variable(_::lastIndexOf($in, 2))
					->isEqualTo('b');
			}, [0, -1]);
	}

	/**
	 * @tags arrays
	 */
	public function testSortedIndex()
	{
		$values = [1,2,3,5];

		$this
			->variable(_::sortedIndex($values, 4))
			->isEqualTo(3);

		$this
			->variable(_::sortedIndex($values, 6))
			->isEqualTo(4);

		$this
			->variable(_::sortedIndex($values, 0))
			->isEqualTo(0);

		$this
			->variable(_::sortedIndex([], 1))
			->isEqualTo(0);
	}

	/**
	 * @tags arrays
	 */
	public function testRange()
	{
		// first form (start 0 stop 5)
		$this
			->array(_::range(5))
			->hasSize(6)
			->isEqualTo([0,1,2,3,4,5]);

		// second form (start 2 stop 4)
		$this
			->array(_::range(2,4))
			->hasSize(3)
			->isEqualTo([2,3,4]);

		// thrid form (start -1 stop 1 step 2)
		$this
			->array(_::range(-1,1,2))
			->hasSize(2)
			->isEqualTo([-1,1]);
	}

	/**
     * Function (uh, ahem) Functions
     * -----------------------------
     */

	/**
	 * @tags functions
	 */
	public function testWrap()
	{
		// it should execute wrapped function when run
		$result = false;
		$fn = function() use (& $result) {
			$result = true;
		};

		$wrapper = _::wrap($fn, function($fn) {
			$fn();
		});

		$wrapper();
		$this
			->boolean($result)
			->isTrue();

		// it should pass parameters along with wrapped function
		$result = null;
		$fn = function($a, $b, $c) use (& $result) {
			$result = $a + $b + $c;
		};

		$wrapper = _::wrap($fn, function($fn, $a, $b, $c) {
			$fn($a, $b, $c);
		});

		$wrapper(1,2,3);
		$this
			->integer($result)
			->isEqualTo(6);

		// it should forward returned value
		$fn = function() {
			return true;
		};

		$wrapper = _::wrap($fn, function($fn) {
			return $fn();
		});

		$this
			->boolean($wrapper())
			->isTrue();
	}

	/**
	 * @tags functions
	 */
	public function testCompose()
	{
		// it should compose functions
		$plusOne = function($i) {
			return $i + 1;
		};

		$double = function($i) {
			return $i * 2;
		};

		$square = function($i) {
			return $i * $i;
		};

		$operation = _::compose($plusOne, $double, $square);
		$this
			->integer($operation(1))
			->isEqualTo(16); // ((1+1) x 2)²

		// second form
		$operation = _::compose([$plusOne, $double, $square]);
		$this
			->integer($operation(1))
			->isEqualTo(16);

		// it should allow multiple parameters
		$plusOne = function($a, $b) {
			return [$a + 1, $b + 1];
		};

		$double = function($a, $b) {
			return [$a * 2, $b * 2];
		};

		$square = function($a, $b) {
			return [$a * $a, $b * $b];
		};

		$operation = _::compose($plusOne, $double, $square);
		$this
			->array($operation(2,3))
			->isEqualTo([36, 64]);
	}

	/**
	 * @tags functions
	 */
	public function testAfter()
	{
		// it should trigger function exection after a couple of tries
		$called = false;
		$fn = _::after(3, function() use (& $called) { $called = true; });

		// call the function 2 times
		$fn(); // nope
		$fn(); // still nope
		$this
			->boolean($called)
			->isFalse();

		// call the function a 3rd time
		$fn(); // fuck yeah!
		$this
			->boolean($called)
			->isTrue();
	}

	/**
	 * @tags functions
	 */
	public function testOnce()
	{
		// it should trigger function execution only once
		$count = 0;
		$fn = _::once(function() use (& $count) { $count++; });

		// call the function 3 times
		for($i=0; $i<3; $i++, $fn());

		$this
			->integer($count)
			->isEqualTo(1);
	}

	/**
	 * @tags functions
	 */
	public function testPartial()
	{
		// it should provide a partial version of the function where last
		// arguments are already provided
		$fn = function($who, $greet, $message) {
			return "$greet $who, $message";
		};

		$partial = _::partial($fn, ["Hello", "what a nice day"]);

		$this
			->string($partial('John'))
			->isEqualTo('Hello John, what a nice day');

		$this
			->string($partial('Peter'))
			->isEqualTo('Hello Peter, what a nice day');
	}

	/**
	 * @tags functions
	 */
	public function testBind()
	{
		// it should be able to bind instances to closure
		$obj = (object)['prop' => 123];
		$fn  = _::bind(function() { return $this->prop; }, $obj);

		$this
			->integer($fn())
			->isEqualTo(123);

		// it should throw an InvalidArgumentException if object is not a valid instance
		$this
			->exception(function() {
				_::bind(function() {}, 'foobar');
			})
			->isInstanceOf('\InvalidArgumentException');

		// it should throw a RuntimeExeption when trying to bind an object to a static closure
		$this
			->exception(function() {
				_::bind(\ClosureClass::getStaticClosure(), (object)['prop' => 123]);
			})
			->isInstanceOf('\RuntimeException');

		// it should behave like partial
		$fn  = _::bind(function($a, $b) { return $this->c + $a + $b; }, (object)['c' => 3], [2]);

		$this
			->integer($fn(1))
			->isEqualTo(6);
	}

	/**
	 * @tags functions
	 */
	public function testBindClass()
	{
		// it should be able to bind class to closure
		$fn = _::bindClass(function() { return self::$d + self::$e + self::$f; }, 'SomeClass');

		$this
			->integer($fn())
			->isEqualTo(15);

		// it should throw an InvalidArgumentException if class doesn't exists
		$this
			->exception(function() {
				_::bindClass(function() {}, 'NoSuchClass');
			})
			->isInstanceOf('\InvalidArgumentException');

		// it should behave like partial
		$fn = _::bindClass(function($a, $b) { return self::$d + $a + $b; }, 'SomeClass', [2]);

		$this
			->integer($fn(1))
			->isEqualTo(7);
	}

	/**
	 * @tags functions
	 */
	public function testBindAll()
	{
		// it should be able to attach several functions to an object at once
		$object = new \ArrayObject([
			'prop' => 1,
			'plusOne' => function() { return $this->prop +1; },
			'plusTwo' => function() { return $this->prop +2; },
		], \ArrayObject::ARRAY_AS_PROPS);

		_::bindAll($object, 'plusOne', 'plusTwo');
		$object['prop'] = 2;

		$this
			->integer($object['plusOne']())
			->isEqualTo(3);

		$this
			->integer($object['plusTwo']())
			->isEqualTo(4);
	}

	/**
	 * @tags functions
	 */
	public function testMemoize()
	{
		// it should return a memoized version of the function
		$fn   = function() { static $count = 0; return ++$count; };
		$memo = _::memoize($fn);

		for($i=0; $i<3; $i++, $memo());

		$this
			->integer($memo())
			->isEqualTo(1);

		// it should be possible to override the hash function
		$hash = function($args) { return array_sum($args); };
		$fn   = function($a, $b, $c) { return $a + $b + $c; };
		$memo = _::memoize($fn, $hash);

		$res[] = $memo(1,2,3);
		$res[] = $memo(1,4,5); // is different from $a because hash function uses all arguments
		$res[] = $memo(1,6,7); // is different from $a because hash function uses all arguments

		$this
			->array($res)
			->isEqualTo([6,10,14]);

		// it should be possible to override the cache
		$cache = [];
		$fn    = function($a) { return $a * $a; };
		$memo  = _::memoize($fn, null, $cache);

		$memo(1); // 1
		$memo(2); // 4
		$memo(3); // 9

		$this
			->array($cache)
			->isEqualTo([1 => 1, 2 => 4, 3 => 9]);

		// it should throw an InvalidArgumentException if cache is not suitable
		$this
			->exception(function() {
				$cache = "hello";
				_::memoize(function() {}, null, $cache);
			})
			->isInstanceOf('\InvalidArgumentException');
	}

	/**
	 * @tags functions
	 */
	public function testThrottle()
	{
		// it should return a throttled version of the function
		$count = 0;
		$fn = _::throttle(function() use (& $count) { $count++; }, 10); // once every 10ms

		$start = microtime(true);
		do {
			$fn();
		} while((microtime(true) - $start) * 1000 < 100); // 100ms

		$this
			->integer($count)
			->isGreaterThan(0)
			->isLessThanOrEqualTo(10);
	}

	/**
     * Object Functions
     * ----------------
     */

	/**
	 * @tags objects
	 */
	public function testKeys()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$this
			->array(_::keys($array))
			->isEqualTo(array_keys($array));

		$object = new \stdClass;
		$object->a = 1;
		$object->b = 2;
		$object->c = 3;
		$this
			->array(_::keys($object))
			->isEqualTo(['a','b','c']);

		$iterator = new \ArrayIterator([1,2,3]);
		$this
			->array(_::keys($iterator))
			->isEqualTo([0,1,2]);
	}

	/**
	 * @tags objects
	 */
	public function testValues()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$this
			->array(_::values($array))
			->isEqualTo([1,2,3]);

		$object = new \stdClass;
		$object->a = 1;
		$object->b = 2;
		$object->c = 3;
		$this
			->array(_::values($object))
			->isEqualTo([1,2,3]);

		$iterator = new \ArrayIterator([1,2,3]);
		$this
			->array(_::values($iterator))
			->isEqualTo([1,2,3]);
	}

	/**
	 * @tags objects
	 */
	public function testPairs()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$this
			->array(_::pairs($array))
			->isEqualTo([['a',1],['b',2],['c',3]]);

		$object = new \stdClass;
		$object->a = 1;
		$object->b = 2;
		$object->c = 3;
		$this
			->array(_::pairs($object))
			->isEqualTo([['a',1],['b',2],['c',3]]);

		$iterator = new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
		$this
			->array(_::pairs($iterator))
			->isEqualTo([['a',1],['b',2],['c',3]]);
	}

	/**
	 * @tags objects
	 */
	public function testInvert()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$this
			->array(_::invert($array))
			->isEqualTo([1 => 'a', 2 => 'b', 3 => 'c']);

		$object = new \stdClass;
		$object->a = 1;
		$object->b = 2;
		$object->c = 3;
		$this
			->object(_::invert($object))
			->isEqualTo((object)[1 => 'a', 2 => 'b', 3 => 'c']);

		$iterator = new \ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
		$this
			->object(_::invert($iterator))
			->isEqualTo((object)[1 => 'a', 2 => 'b', 3 => 'c']);
	}

	/**
	 * @tags objects
	 */
	public function testFunctions()
	{
		// it should extract function names from lists
		$list = [
			'prop' => 1,
			'foo' => function() {},
			'bar' => function() {},
			'baz' => function() {},
		];

		$this
			->array(_::functions($list))
			->isEqualTo(['foo','bar','baz']);

		$this
			->array(_::functions((object)$list))
			->isEqualTo(['foo','bar','baz']);

		// it should extract function names from classes
		$this
			->array(_::functions("\SomeClass"))
			->isEqualTo(['foo','bar','baz']);

		// it should extract function names from instances
		$object = new \SomeClass;

		$this
			->array(_::functions($object))
			->isEqualTo(['foo','bar','baz']);
	}

	/**
	 * @tags objects
	 */
	public function testExtend()
	{
		// it should be able to extend an object, and be type tolerant
		$object = new \stdClass;
		$object->a = 1;

		$this
			->object(_::extend($object, ['b' => 2], new \ArrayIterator(['c' => 3])))
			->isEqualTo((object)['a' => 1, 'b' => 2, 'c' => 3]);

		// it should override the destination object with values from the sources, the last source being prioritary
		$this
			->array(_::extend([1], [2], [3], [4], [5]))
			->isEqualTo([5]);
	}

	/**
	 * @tags objects
	 */
	public function testPick()
	{
		// it should be able to extract certain keys from a list and preserve the general type
		$this
			->array(_::pick(['a' => 1, 'b' => 2, 'c' => 3], ['b', 'c']))
			->isEqualTo(['b' => 2, 'c' => 3]);

		// 2nd form
		$this
			->array(_::pick(['a' => 1, 'b' => 2, 'c' => 3], 'b', 'c'))
			->isEqualTo(['b' => 2, 'c' => 3]);

		// it shoud ignore keys that are not part of the list/object
		$object = new \stdClass;
		$object->a = 1;

		$this
			->object(_::pick($object, 'a', 'b', 'c'))
			->isEqualTo((object)['a' => 1]);

		// it should preserve keys order of picked keys
		$this
			->array(_::pick(['a' => 1, 'b' => 2, 'c' => 3], 'c', 'b', 'a'))
			->keys
				->isEqualTo(['c', 'b', 'a']);
	}

	/**
	 * @tags objects
	 */
	public function testOmit()
	{
		// it should be able to omit certain keys from a list and preserve the general type
		$this
			->array(_::omit(['a' => 1, 'b' => 2, 'c' => 3], ['b', 'c']))
			->isEqualTo(['a' => 1]);

		// 2nd form
		$this
			->array(_::omit(['a' => 1, 'b' => 2, 'c' => 3], 'b', 'c'))
			->isEqualTo(['a' => 1]);

		// it shoud ignore keys that are not part of the list/object
		$object = new \stdClass;
		$object->a = 1;

		$this
			->object(_::omit($object, 'b', 'c'))
			->isEqualTo((object)['a' => 1]);
	}

	/**
	 * @tags objects
	 */
	public function testDefaults()
	{
		// it should be able to extend an object, and be type tolerant
		$object = new \stdClass;
		$object->a = 1;

		$this
			->object(_::defaults($object, ['b' => 2], new \ArrayIterator(['b' => 3, 'c' => 3])))
			->isEqualTo((object)['a' => 1, 'b' => 2, 'c' => 3]);

		// it should override the destination object with values from the sources, the destination being prioritary
		$this
			->array(_::defaults([1], [2], [3], [4], [5]))
			->isEqualTo([1]);
	}

	/**
	 * @tags objects
	 */
	public function testDuplicate()
	{
		$object = new \stdClass;
		$object->a = 1;

		$this
			->object(_::duplicate($object))
			->isCloneOf($object);

		$array = [1,2,3];

		$this
			->array(_::duplicate($array))
			->isEqualTo($array);
	}

	/**
	 * @tags objects
	 */
	public function testTap()
	{
		$value = "hello";

		$this
			->variable(_::tap($value, function(& $v) { $v = "world"; }))
			->isEqualTo("world");
	}

	/**
	 * @tags objects
	 */
	public function testHas()
	{
		$this
			->boolean(_::has([], 'any'))
			->isFalse();

		$this
			->boolean(_::has(new \stdClass, 'any'))
			->isFalse();

		$this
			->boolean(_::has([null => null], null))
			->isTrue();

		$this
			->boolean(_::has([0 => 0], false))
			->isTrue();

		$this
			->boolean(_::has(['foo' => 1], 'bar'))
			->isFalse();

		$this
			->boolean(_::has((object)['a' => 1], 'a'))
			->isTrue();

		$this
			->boolean(_::has([1,2,3], 2))
			->isTrue();

		$this
			->boolean(_::has(false, false))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testGet()
	{
		$this
			->variable(_::get([], 'any'))
			->isNull();

		$this
			->variable(_::get(new \stdClass, 'any'))
			->isNull();

		$this
			->variable(_::get([null => null], null))
			->isNull();

		$this
			->variable(_::get([0 => 0], false))
			->isEqualTo(0);

		$this
			->variable(_::get(['foo' => 1], 'bar'))
			->isNull();

		$this
			->variable(_::get((object)['a' => 1], 'a'))
			->isEqualTo(1);

		$this
			->variable(_::get([1,2,3], 2))
			->isEqualTo(3);

		$this
			->variable(_::get(false, false))
			->isNull();
	}

	/**
	 * @tags objects
	 */
	public function testSet()
	{
		$array    = [];
		$object   = new \stdClass;
		$iterator = new \ArrayIterator([]);

		$this
			->array(_::set($array, 'foo', 'bar'))
			->isEqualTo(['foo' => 'bar']);

		$array = [1,2,3];
		$this
			->array(_::set($array, 3, 4))
			->isEqualTo([1,2,3,4]);

		$this
			->object(_::set($object, 'foo', 'bar'))
			->isEqualTo((object)['foo' => 'bar']);

		$this
			->array(_::set($iterator, 'foo', 'bar')->getArrayCopy())
			->isEqualTo(['foo' => 'bar']);
	}

	/**
	 * @tags objects
	 */
	public function testIs()
	{
		$this
			->boolean(_::is(null, _::TYPE_NULL))
			->isTrue();

		$this
			->boolean(_::is(true, _::TYPE_BOOLEAN))
			->isTrue();

		$this
			->boolean(_::is(1, _::TYPE_INTEGER))
			->isTrue();

		$this
			->boolean(_::is(1.1, _::TYPE_FLOAT))
			->isTrue();

		$this
			->boolean(_::is(acos(8), _::TYPE_NAN))
			->isTrue();

		$this
			->boolean(_::is("foo", _::TYPE_STRING))
			->isTrue();

		$this
			->boolean(_::is([], _::TYPE_ARRAY))
			->isTrue();

		$this
			->boolean(_::is(new \stdClass, _::TYPE_OBJECT))
			->isTrue();

		$this
			->boolean(_::is(fopen(__FILE__, 'r'), _::TYPE_RESOURCE))
			->isTrue();

		$this
			->boolean(_::is(new \stdClass, 'stdClass'))
			->isTrue();

		$this
			->boolean(_::is(new \ArrayObject, _::TYPE_ARRAY))
			->isTrue();

		$this
			->boolean(_::is(new \SomeClass, 'stdClass', 'ArrayObject', 'SomeClass'))
			->isTrue();
	}

	/**
	 * @tags objects
	 */
	public function testIsEqual()
	{
		$true = [[1,1], ['a','a'], [1.1,1.1], [new \stdClass, new \stdClass], [[],[]], [['a'],['a']], [null,null],
			[true,true]];

		foreach ($true as $values) {
			list($a, $b) = $values;
			$this
				->boolean(_::isEqual($a, $b))
				->isTrue();
		}

		$false = [[1,2], ['a','b'], [1.1,1.2], [new \stdClass, new \SomeClass], [[''],['','']], [['a'],['b']],
			[true,false]];

		foreach ($false as $values) {
			list($a, $b) = $values;
			$this
				->boolean(_::isEqual($a, $b))
				->isFalse();
		}

		// deep object equality lol
		$a = (object)[
			'foo' => [
				'bar' => (object)[
					'baz' => [1,2,3],
				]
			]
		];
		$b = (object)[
			'foo' => [
				'bar' => (object)[
					'baz' => [1,2,3],
				]
			]
		];

		$this
			->boolean(_::isEqual($a, $b))
			->isTrue();

		$b->foo['bar']->baz[1] = 4;

		$this
			->boolean(_::isEqual($a, $b))
			->isFalse();

		// cyclic structures
		$a = ['foo' => & $a];
		$b = ['foo' => & $b];

		$this
			->boolean(_::isEqual($a, $b))
			->isTrue();

		$b = ['bar' => & $b];

		$this
			->boolean(_::isEqual($a, $b))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsEmpty()
	{
		$this
			->boolean(_::isEmpty(null))
			->isTrue();

		$this
			->boolean(_::isEmpty(false))
			->isTrue();

		$this
			->boolean(_::isEmpty(123))
			->isFalse();

		$this
			->boolean(_::isEmpty(new \stdClass))
			->isTrue();

		$this
			->boolean(_::isEmpty([]))
			->isTrue();
	}

	/**
	 * @tags objects
	 */
	public function testIsArray()
	{
		$this
			->boolean(_::isArray([]))
			->isTrue();

		$this
			->boolean(_::isArray(new \ArrayObject))
			->isTrue();

		$this
			->boolean(_::isArray('hello'))
			->isFalse();

		$this
			->boolean(_::isArray(null))
			->isFalse();

		$this
			->boolean(_::isArray(new \ArrayObject, true))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsObject()
	{
		$this
			->boolean(_::isObject(new \stdClass))
			->isTrue();

		$this
			->boolean(_::isObject(null))
			->isFalse();

		$this
			->boolean(_::isObject("hello"))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsFunction()
	{
		$this
			->boolean(_::isFunction(function() {}))
			->isTrue();

		$this
			->boolean(_::isFunction(create_function('','')))
			->isTrue();

		$instance = new \SomeClass;
		$this
			->boolean(_::isFunction([$instance,'foo']))
			->isTrue();

		$this
			->boolean(_::isFunction(['SomeClass', 'bar']))
			->isTrue();

		$this
			->boolean(_::isFunction('SomeClass::bar'))
			->isTrue();

		$this
			->boolean(_::isFunction('mt_rand'))
			->isTrue();

		$this
			->boolean(_::isFunction(null))
			->isFalse();

		$this
			->boolean(_::isFunction("hello"))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsNumber()
	{
		$this
			->boolean(_::isNumber(1))
			->isTrue();

		$this
			->boolean(_::isNumber(.1))
			->isTrue();

		$this
			->boolean(_::isNumber('-1'))
			->isTrue();

		$this
			->boolean(_::isNumber(pi()))
			->isTrue();

		$this
			->boolean(_::isNumber(acos(1.01)))
			->isFalse();

		$this
			->boolean(_::isNumber(new \SplInt))
			->isTrue();

		$this
			->boolean(_::isNumber(new \SplFloat))
			->isTrue();

		$this
			->boolean(_::isNumber(new \SplInt, true))
			->isFalse();

		$this
			->boolean(_::isNumber(new \SplFloat, true))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsInteger()
	{
		$this
			->boolean(_::isInteger(1))
			->isTrue();

		$this
			->boolean(_::isInteger(-1))
			->isTrue();

		$this
			->boolean(_::isInteger(new \SplInt))
			->isTrue();

		$this
			->boolean(_::isInteger(new \SplInt, true))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsFloat()
	{
		$this
			->boolean(_::isFloat(.1))
			->isTrue();

		$this
			->boolean(_::isFloat(pi()))
			->isTrue();

		$this
			->boolean(_::isFloat(new \SplFloat))
			->isTrue();

		$this
			->boolean(_::isFloat(new \SplFloat, true))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsString()
	{
		$this
			->boolean(_::isString('hello'))
			->isTrue();

		$this
			->boolean(_::isString(new \SplString))
			->isTrue();

		$this
			->boolean(_::isString(new \SplString, true))
			->isFalse();

		$this
			->boolean(_::isString(new \MagicMethods))
			->isTrue();

		$this
			->boolean(_::isString(123))
			->isFalse();

		$this
			->boolean(_::isString(new \stdClass))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsDate()
	{
		// required or E_WARNING *sigh*
		date_default_timezone_set('Europe/Paris');

		$this
			->boolean(_::isDate('01-01-1970')) // EPOCH
			->isTrue();

		$this
			->boolean(_::isDate('tomorrow')) // strtotime !
			->isTrue();

		$this
			->boolean(_::isDate(new \DateTime))
			->isTrue();

		$this
			->boolean(_::isDate('hello'))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsRegExp()
	{
		$this
			->boolean(_::isRegExp('/\w+/mi'))
			->isTrue();

		$this
			->boolean(_::isRegExp('w+'))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsFinite()
	{
		$this
			->boolean(_::isFinite(1))
			->isTrue();

		$this
			->boolean(_::isFinite(-9e1000))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsNaN()
	{
		$this
			->boolean(_::isNan(acos(1.01)))
			->isTrue();

		$this
			->boolean(_::isNan(1.123))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsBoolean()
	{
		$this
			->boolean(_::isBoolean(true))
			->isTrue();

		$this
			->boolean(_::isBoolean(false))
			->isTrue();

		$this
			->boolean(_::isBoolean(null))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsNull()
	{
		$this
			->boolean(_::isNull(null))
			->isTrue();

		$this
			->boolean(_::isNull(false))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsScalar()
	{
		$this
			->boolean(_::isScalar(123))
			->isTrue();

		$this
			->boolean(_::isScalar("foo"))
			->isTrue();

		$this
			->boolean(_::isScalar(new \stdClass))
			->isFalse();

		$this
			->boolean(_::isScalar([]))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsTraversable()
	{
		$this
			->typeTolerant([], null, function($in, $out) {
				$this
					->boolean(_::isTraversable($in))
					->isTrue();
			}, [1, -1]);

		$this
			->boolean(_::isTraversable(false))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testIsResource()
	{
		$this
			->boolean(_::isResource(fopen(__FILE__, 'r')))
			->isTrue();

		$this
			->boolean(_::isResource(__FILE__))
			->isFalse();
	}

	/**
	 * @tags objects
	 */
	public function testGetType()
	{
		$this
			->string(_::getType(123))
			->isEqualTo(_::TYPE_INTEGER);

		$this
			->string(_::getType(1.1))
			->isEqualTo(_::TYPE_FLOAT);

		$this
			->string(_::getType(''))
			->isEqualTo(_::TYPE_STRING);

		$this
			->string(_::getType([]))
			->isEqualTo(_::TYPE_ARRAY);

		$this
			->string(_::getType(new \stdClass))
			->isEqualTo('stdClass');

		$this
			->string(_::getType(new \stdClass, false))
			->isEqualTo(_::TYPE_OBJECT);
	}

	/**
	 * @tags objects
	 */
	public function testTypeOf()
	{
		$this
			->string(_::typeOf(null))
			->isEqualTo(_::TYPE_NULL);

		$this
			->string(_::typeOf(true))
			->isEqualTo(_::TYPE_BOOLEAN);

		$this
			->string(_::typeOf(1))
			->isEqualTo(_::TYPE_INTEGER);

		$this
			->string(_::typeOf(1.1))
			->isEqualTo(_::TYPE_FLOAT);

		$this
			->string(_::typeOf(acos(8)))
			->isEqualTo(_::TYPE_NAN);

		$this
			->string(_::typeOf("foo"))
			->isEqualTo(_::TYPE_STRING);

		$this
			->string(_::typeOf([]))
			->isEqualTo(_::TYPE_ARRAY);

		$this
			->string(_::typeOf(new \stdClass, false))
			->isEqualTo(_::TYPE_OBJECT);

		$this
			->string(_::typeOf(fopen(__FILE__, 'r')))
			->isEqualTo(_::TYPE_RESOURCE);

		$this
			->string(_::typeOf(new \stdClass, true))
			->isEqualTo('stdClass');

		$this
			->string(_::typeOf(new \ArrayObject))
			->isEqualTo(_::TYPE_ARRAY);

		$this
			->string(_::typeOf(new \SomeClass))
			->isEqualTo('SomeClass');
	}

	/**
	 * Utility Functions
	 * -----------------
	 */

	/**
	 * @tags utilities
	 */
	public function testIdentity()
	{
		// it should return the exact same value
		foreach([null,0,1,false,true,"A","b"] as $val)
			$this
				->variable(_::identity($val))
				->isIdenticalTo($val);
	}

	/**
	 * @tags utilities
	 */
	public function testTimes()
	{
		$fn = function() { static $num = 0; return ++$num; };

		$this
			->array(_::times(3, $fn))
			->isEqualTo([1,2,3]);
	}

	/**
	 * @tags utilities
	 */
	public function testRandom()
	{
		$this
			->integer(_::random(10))
			->isLessThanOrEqualTo(10)
			->isGreaterThanOrEqualTo(0);

		$this
			->integer(_::random(5,8))
			->isLessThanOrEqualTo(8)
			->isGreaterThanOrEqualTo(5);
	}

	/**
	 * @tags utilities
	 */
	public function testMixin()
	{
		_::mixin(['foo' => function() { return 'bar'; }]);

		$this
			->string(_::foo())
			->isEqualTo('bar');
	}

	/**
	 * @tags utilities
	 */
	public function testProvide()
	{
		$fn = _::provide('random');

		$this
			->variable($fn)
			->isCallable();

		$this
			->integer($fn(10))
			->isGreaterThanOrEqualTo(0)
			->isLessThanOrEqualTo(10);
	}

	/**
	 * @tags utilities
	 */
	public function testUniqueId()
	{
		$a = _::uniqueId();
		$b = _::uniqueId();

		$this
			->variable($a)
			->isNotEqualTo($b);
	}

	/**
	 * @tags utilities
	 */
	public function testEscape()
	{
		$this
			->string(_::escape('<tag>Something</tag>'))
			->isEqualTo('&lt;tag&gt;Something&lt;/tag&gt;');
	}

	/**
	 * @tags utilities
	 */
	public function testUnescape()
	{
		$this
			->string(_::unescape('&lt;tag&gt;Something&lt;/tag&gt;'))
			->isEqualTo('<tag>Something</tag>');
	}

	/**
	 * @tags utilities
	 */
	public function testResult()
	{
		$object = (object)[
			'prop'   => 'hello',
			'method' => function() { return $this->prop; },
		];

		$this
			->string(_::result($object, 'prop'))
			->isEqualTo('hello');

		$this
			->string(_::result($object, 'method'))
			->isEqualTo('hello');
	}

	/**
	 * @tags utilities
	 */
	public function testTemplate()
	{
		// interpolate
		$this
			->string(_::template('<%=$a%>', ['a' => "hello"]))
			->isEqualTo('hello');

		// escape
		$this
			->string(_::template('<%-$b%>', ['b' => "<tag>hello</tag>"]))
			->isEqualTo('&lt;tag&gt;hello&lt;/tag&gt;');

		// evaluate
		$this
			->string(_::template('<% echo $c %>', ['c' => "hello"]))
			->isEqualTo('hello');

		// mix
		$this
			->string(_::template('<% for($i=1; $i<=$d; $i++): %>i<%=$i%><% endfor %>', ['d' => 3]))
			->isEqualTo('i1i2i3');

		// function
		$fn = _::template('<%=$a%>');

		$this
			->string($fn(['a' => 1]))
			->isEqualTo('1');

		$this
			->string($fn(['a' => 2]))
			->isEqualTo('2');

		$this
			->string($fn(['a' => 3]))
			->isEqualTo('3');
	}

	/**
     * Chaining
     * --------
     */

	/**
	 * @dataProvider peopleDataProvider
	 * @tags chaining
	 */
	public function testChain($people, $type, $meta)
	{
		$actors = _::chain($people)
			->sortBy(function($actor) { return $actor->born; })
			->where(['profession' => 'actor'])
			->pluck('name')
			->walk(function(& $name) { $name = strtolower($name); })
			->value();

		$this
			->array($actors)
			->isEqualTo([
				'nportman'    => 'nathalie portman',
				'ldicaprio'   => 'leonardo dicaprio',
				'mfreeman'    => 'morgan freeman',
				'jnicholson'  => 'jack nicholson',
			]);
	}

	/**
	 * Data providers
	 * --------------
	 */

	public function peopleDataProvider()
	{
		$people = [
			'jnicholson'  => ['name' => 'Jack Nicholson',    'born' => 1937, 'profession' => 'actor'    ],
			'mfreeman'    => ['name' => 'Morgan Freeman',    'born' => 1937, 'profession' => 'actor'    ],
			'ldicaprio'   => ['name' => 'Leonardo Dicaprio', 'born' => 1974, 'profession' => 'actor'    ],
			'nportman'    => ['name' => 'Nathalie Portman',  'born' => 1981, 'profession' => 'actor'    ],
			'rscott'      => ['name' => 'Ridley Scott',      'born' => 1937, 'profession' => 'producer' ],
		];

		$meta = [
			'keys'        => array_keys($people),
			'names'       => array_column($people, 'name'),
			'ages'        => array_merge(array_keys($people), array_map(function($p) { return 2014 - $p['born']; }, array_column($people, 'born'))),
			'professions' => array_unique(array_column($people, 'profession')),
			'count'       => count($people),
		];

		return [
			['people' => $people,                          'type' => 'Array',    'meta' => $meta],
			['people' => static::toObject($people, true),  'type' => 'Object',   'meta' => $meta],
			['people' => static::toIterator($people, true),'type' => 'Iterator', 'meta' => $meta],
		];
	}

	/**
	 * Internal Test Helpers
	 * ---------------------
	 */

	public static function toArray($array, $deep = false)
	{
		return !$deep
			? (array)$array
			: array_map(function($v) { return !is_scalar($v) ? (array)$v : $v; }, $array);
	}

	public static function toObject(array $array, $deep = false)
	{
		return !$deep
			? (object)$array
			: (object)array_map(function($v) { return is_array($v) ? (object)$v : $v; }, $array);
	}

	public static function toIterator(array $array, $deep = false)
	{
		$class = '\ArrayIterator';

		return !$deep
			? new $class($array)
			: new $class(array_map(function($v) use ($class) { return is_array($v) ? new $class($v) : $v; }, $array));
	}

	public function typeTolerant($input, $output, \Closure $function, $opts = null)
	{
		isset($opts) || $opts = [0,0];
		list($convIn, $convOut) = [$opts[0] > -1, $opts[1] > -1];
		list($deepIn, $deepOut) = [$opts[0] == 1, $opts[1] == 1];
		$function = $function->bindTo($this);

		foreach(['Array','Object','Iterator'] as $type)
			$function(
				$convIn  ? self::{"to$type"}($input,  $deepIn)  : $input,
				$convOut ? self::{"to$type"}($output, $deepOut) : $output,
				$type
			);
	}
}
