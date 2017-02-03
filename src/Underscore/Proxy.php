<?php

namespace Underscore;

/**
 * Underscore.php
 * https: *github.com/bdelespierre/underscore.php
 * (c) 2013-2017 Benjamin Delespierre
 * Underscore may be freely distributed under the LGPL license.
 *
 * Proxy helps you to chain modifications on a value using a set of functions. These fonctions can be either
 * bundled into an instance or in a simple hashmap.
 */
class Proxy
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var object,array
     */
    protected $service;

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
        switch (true) {
            case is_callable([$this->service, $method]):
            case method_exists($this->service, $method):
                $method = [$this->service, $method];
                break;

            case isset($this->service[$method]) && is_callable($this->service[$method]):
                $method = $this->service[$method];
                break;

            case isset($this->service->$method) && is_callable($this->service->$method):
                $method = $this->service->$method;
                break;

            default:
                throw new BadMethodCallException("no such method {$method}");
        }

        array_unshift($args, $this->value);
        $this->value = $method(...$args);
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
