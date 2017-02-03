<?php

require_once __DIR__ . '/Underscore/Underscore.php';
require_once __DIR__ . '/Underscore/Exception/BreakException.php';
require_once __DIR__ . '/Underscore/Bridge.php';

/**
 * Underscore.php
 * https://github.com/bdelespierre/underscore.php
 * (c) 2013-2017 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 */
use Underscore\Underscore as _;

// register class forgery as autoloading function
spl_autoload_register(function ($classname) {
    try {
        // by your powers combined!
        return _::forge($classname);
    } catch (RuntimeException $e) {
        // ...we failed to summon Captain Planet :(
        return false;
    }
});
