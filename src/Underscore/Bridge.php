<?php

namespace Underscore;

/**
 * Underscore.php 0.0.1
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
    protected $_value;

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
        $this->_value   = $initialValue;
        $this->_service = $service;
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
        if (method_exists($this->_service, $method) || is_callable([$this->_service, $method]))
            $method = [$this->_service, $method];
        elseif (isset($this->_service[$method]) && is_callable($this->_service[$method]))
            $method = $this->_service[$method];
        elseif (isset($this->_service->$method) && is_callable($this->_service->$method))
            $method = $this->_service->$method;
        else
            throw new  \BadMethodCallException("no such method {$method}");

        // reminder: call_user_func does _not_ allow passing by reference !
        switch (count($args)) {
            case 0: $this->_value = $method($this->_value); break;
            case 1: $this->_value = $method($this->_value, $args[0]); break;
            case 2: $this->_value = $method($this->_value, $args[0], $args[1]); break;
            case 3: $this->_value = $method($this->_value, $args[0], $args[1], $args[2]); break;
            case 4: $this->_value = $method($this->_value, $args[0], $args[1], $args[2], $args[3]); break;
            case 5: $this->_value = $method($this->_value, $args[0], $args[1], $args[2], $args[3], $args[4]); break;
            case 6: $this->_value = $method($this->_value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;
            case 7: $this->_value = $method($this->_value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]); break;
            case 8: $this->_value = $method($this->_value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]); break;
            case 9: $this->_value = $method($this->_value, $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]); break;
            default: $this->_value = call_user_func_array($method, array_merge($this->_value, $args)); break;
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
        return $this->_value;
    }
}