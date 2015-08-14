<?php

require_once dirname(__DIR__) . "/src/Underscore/Underscore.php";
require_once dirname(__DIR__) . "/src/Underscore/Bridge.php";

use Underscore\Underscore as _;

exit(main($argc, $argv));

function main($argc, $argv)
{
	ini_set('display_errors', 0);

	if (empty($argv[1]))
		return 1;

	switch ($argv[1]) {
		case 'functions': return _makeFunctions(); break;
		case 'markdown':  return _makeMarkdown();  break;
	}
}

function _makeFunctions()
{
	$template     = include __DIR__ . '/templates.php';
	$funcTemplate = _::template($template['functions']['function']);
	$content      = "";

	foreach (_getMethods() as $function)
		$content .= $funcTemplate(compact('function'));

	$build_version = _::VERSION;
	$last_update   = date('r');

	return _::template($template['functions']['main'], compact('content', 'build_version', 'last_update'));
}

function _makeMarkdown()
{
	$template     = include __DIR__ . '/templates.php';
	$catTemplate  = _::template($template['markdown']['category']);
	$funcTemplate = _::template($template['markdown']['function']);
	$content      = "";

	$methodsByCategory = _::groupBy(_getMethods(), function ($method) {
		return $method->category;
	});

	$formatMenuEntry = function ($name) {
		return _anchor($name);
	};

	foreach ($methodsByCategory as $category => $methods) {
		$categoryContent = "";

		foreach ($methods as $function)
			$categoryContent .= $funcTemplate(compact('function'));

		$content .= $catTemplate([
			'category'  => $category,
			'content'   => $categoryContent,
			'functions' => _::chain($methods)->pluck('name')->map($formatMenuEntry)->value(),
		]);
	}

	$installation = "";
	$usage        = "";
	$menu         = _::template($template['markdown']['menu'], [
		'categories' => _::map($methodsByCategory, function ($methods) {
			return _::pluck($methods, 'name');
		}),
	]);

	return _::template($template['markdown']['main'], compact('menu', 'content'));
}

function _getMethods()
{
	$reflect  = new ReflectionClass('Underscore\Underscore');
	$examples = _getExamples();

	return _::chain($reflect->getMethods())
		->map(function ($method) {
			$parameters    = _getParameters($method);
			$documentation = _getDocumentation($method, $parameters);

			return (object)[
				'name'        => $method->name,
				'isPublic'    => $method->isPublic() && strpos($method->name, '__') === false,
				'parameters'  => $parameters,
				'description' => $documentation->description,
				'category'    => $documentation->category,
				'throws'      => $documentation->throws,
				'returns'     => $documentation->returns,
				'aliasOf'     => $documentation->aliasOf,
				'aliases'     => [],
				'reflect'     => $method,
				'prototype'   => "_::{$method->name}(" . implode(',', _::keys($parameters)) . ")",
			];
		})
		->indexBy('name')
		->filter(function ($method) {
			return $method->isPublic;
		})
		->invoke(function () use ($examples) {
			$this->argumentsAsString             = implode(', ', _::pluck($this->parameters, 'asString'));
			$this->argumentsAsStringNoRef        = implode(', ', _::pluck($this->parameters, 'asStringNoRef'));
			$this->argumentsAsStringNoRefNoValue = implode(', ', _::pluck($this->parameters, 'asStringNoRefNoValue'));
			$this->examples = (array)_::get($examples, $this->name, []);
		})
		->tap(function (& $methods) {
			foreach ($methods as $method)
				if ($method->aliasOf)
					$methods[$method->aliasOf]->aliases[] = $method->name;
		})
		->reject(function ($method) {
			return $method->aliasOf;
		})
		->value();
}

function _getExamples()
{
	$file = new SplFileObject(__DIR__ . '/examples.php');
	$examples = [];
	$current  = '';

	foreach ($file as $line) {
		if (preg_match('~^(\w+):$~', $line, $matches)) {
			$examples[$matches[1]] = '';
			$current = & $examples[$matches[1]];
			continue;
		}

		$current .= $line;
	}

	return _::map($examples, function ($ex) { return trim($ex); });
}

function _getParameters(ReflectionMethod $method)
{
	return _::chain($method->getParameters())
		->map(function ($parameter) {
			return (object)[
				'name'            => $parameter->name,
				'isReference'     => $parameter->isPassedByReference(),
				'hasDefaultValue' => $parameter->isDefaultValueAvailable(),
				'default'         => $parameter->isDefaultValueAvailable() ? _valueToString($parameter->getDefaultValue()) : null,
				'type'            => '',
				'description'     => '',
				'reflect'         => $parameter,
			];
		})
		->invoke(function () {
			$this->asStringNoRefNoValue = "\${$this->name}";
			$this->asStringNoRef        = "\${$this->name}" . ($this->hasDefaultValue ? "={$this->default}" : '');
			$this->asString             = ($this->isReference ? '&' : '') . "\${$this->name}" .
										  ($this->hasDefaultValue ? "={$this->default}" : '');
		})
		->indexBy('name')
		->value();
}

function _getDocumentation(ReflectionMethod $method, & $parameters)
{
	preg_match_all('~\* ([^\n\r]+)~m', $method->getDocComment(), $matches);

	$dox = (object)[
		'description' => '',
		'category'    => 'Uncategorized',
		'throws'      => [],
		'returns'     => [],
		'aliasOf'     => null,
	];

	if (empty($matches[1]))
		return $dox;

	foreach ($matches[1] as $line) {
		if ($line[0] != '@') {
			$dox->description .= "$line ";
			continue;
		}

		$tag = substr($line, 0, $pos = strpos($line, ' '));
		$val = substr($line, $pos+1);

		switch ($tag) {
			case '@category':
				$dox->category = $val;
				break;
			case '@param':
				if (preg_match('~([\w,]+) \$([\w]+) (.+)?~', $val, $matches)) {
					list(,$type,$name,$description) = $matches + [null,'','',''];
					empty($parameters[$name]) && $parameters[$name] = (object)[];
					$parameters[$name]->name = $name;
					$parameters[$name]->type = $type;
					$parameters[$name]->description = $description;
				}
				break;
			case '@throws':
				if (preg_match('~([\w]+) (.+)~', $val, $matches)) {
					list(,$exception,$description) = $matches + [null,'',''];
					$dox->throws[] = compact('exception', 'description');
				}
				break;
			case '@return':
				$dox->returns = $val;
				break;

			case '@see':
				if (preg_match('~Underscore::(\w+)~', $val, $matches)) {
					list(,$aliasOf) = $matches + [null,''];
					$dox->aliasOf = $aliasOf;
				}
				break;
		}
	}

	$dox->description = trim($dox->description);

	return $dox;
}

function _valueToString($value)
{
	ob_start();
	var_export($value);
	return ($str = str_replace(["\r","\n"], '', ob_get_clean())) == 'array ()' ? '[]' : $str;
}

function _anchor($value)
{
	return "[$value](#" . _mdAnchor($value) . ")";
}

function _mdAnchor($value)
{
	return strtolower(str_replace(' ', '-', preg_replace('/[^a-z0-9 ]/i', '', $value)));
}