<?php
/**
 * This file is part of Underscore.php
 *
 * Copyright (c) 2013 Benjamin Delespierre
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @package Underscore
 * @author Benjamin Delespierre
 */
class Bridge {

    protected $_value;
    protected $_service;

    public $chain = false;

    public function __construct ($initialValue, $service) {
        $this->_value   = $initialValue;
        $this->_service = $service;
    }

    public function __call ($method, $args) {
        if (method_exists($this->_service, $method) || is_callable([$this->_service, $method]))
            $method = [$this->_service, $method];
        elseif (isset($this->_service[$method]) && is_callable($this->_service[$method]))
            $method = $this->_service[$method];
        elseif (isset($this->_service->$method) && is_callable($this->_service->$method))
            $method = $this->_service->$method;
        else
            throw new BadMethodCallException("method $method isn't part of " . get_class($this->_service));

        array_unshift($args, $this->_value);
        $this->_value = call_user_func_array($method, $args);

        return $this->chain ? $this : $this->value();
    }

    public function value () {
        return $this->_value;
    }
}