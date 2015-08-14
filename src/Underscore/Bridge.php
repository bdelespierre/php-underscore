<?php

namespace Underscore;

/**
 * Underscore.php
 * https: *github.com/bdelespierre/underscore.php
 * (c) 2013-2014 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 *
 * Bridge helps you to chain modifications on a value using a set of functions. These fonctions can be either
 * bundled into an instance or in a simple hashmap.
 */
class Bridge
{
    /**
     * @internal
     * @var mixed
     */
    protected $value;

    /**
     * @internal
     * @var object,array
     */
    protected $_service;

    /**
     * Constructor
     *
     * @param mixed $initialValue the initial state
     * @param object,array $service the service object
     */
    public function __construct($initialValue, $service)
    {
        $this->value   = $initialValue;
        $this->service = $service;
    }

    /**
     * Call any service method providing internal value as first parameter. The bridge instance is always
     *
     * @param string $method the service method to call
     * @param array $args the method's arguments
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->service, $method) || is_callable([$this->service, $method]))
            $method = [$this->service, $method];
        elseif (isset($this->service[$method]) && is_callable($this->service[$method]))
            $method = $this->service[$method];
        elseif (isset($this->service->$method) && is_callable($this->service->$method))
            $method = $this->service->$method;
        else
            throw new  \BadMethodCallException("no such method {$method}");

        // reminder: call_user_func does _not_ allow passing by reference !
        // this will be fixed soon (as PHP 5.5 becomes LTS) with variadics
        switch (count($args)) {
            case 0: $this->value = $method($this->value); break;
            case 1: $this->value = $method($this->value, $args[0]); break;
            case 2: $this->value = $method($this->value, $args[0], $args[1]); break;
            case 3: $this->value = $method($this->value, $args[0], $args[1], $args[2]); break;
            case 4: $this->value = $method($this->value, $args[0], $args[1], $args[2], $args[3]); break;
            case 5: $this->value = $method($this->value, $args[0], $args[1], $args[2], $args[3], $args[4]); break;
            case 6: $this->value = $method($this->value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;
            case 7: $this->value = $method($this->value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]); break;
            case 8: $this->value = $method($this->value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]); break;
            case 9: $this->value = $method($this->value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]); break;
            default: $this->value = call_user_func_array($method, array_merge($this->value, $args)); break;
        }

        return $this;
    }

    /**
     * Retrieve the current value state.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}