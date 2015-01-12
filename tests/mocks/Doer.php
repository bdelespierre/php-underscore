<?php

class Doer
{
	public function __construct(\Closure $function)
	{
		$this->fn = $function->bindTo($this);
	}

	public function doYourJob()
	{
		call_user_func_array($this->fn, func_get_args());
	}

	public static function create($howMany, \Closure $function)
	{
		if ($howMany < 1)
			throw new \InvalidArgumentException("cmon, seriously?");

		while($howMany--)
			$doers[] = new static($function);

		return $doers;
	}
}