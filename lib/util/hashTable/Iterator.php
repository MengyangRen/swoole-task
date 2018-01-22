<?php

namespace SP\Set;

class Iterator implements \Iterator {
    private $position = 0;

    public function __construct(&$array) {
        $this->array = &$array;
        $this->rewind();
    }

    function rewind() {
        $this->position = 0;
    	return reset($this->array);
    }

    function current() {
        return key($this->array);
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
        return next($this->array);
    }

    function valid() {
        return $this->current() !== NULL;
    }
}