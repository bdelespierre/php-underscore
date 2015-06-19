<?php

require_once __DIR__ . '/Underscore/Underscore.php';
require_once __DIR__ . '/Underscore/Bridge.php';

class_alias('Underscore\Underscore', '_');

// register class forgery as autoloading function
spl_autoload_register(function($classname) {
	try {
		// by your powers combined!
		return _::forge($classname);
	} catch (RuntimeException $e) {
		// ...we failed to summon Captain Planet :(
		return false;
	}
});