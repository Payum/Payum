<?php

namespace Payum\Core\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use ReturnTypeWillChange;

class ArrayObject implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     */
    protected $details = [];

    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->details);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->details[$offset];
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->details[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->details[$offset]);
    }

    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->details);
    }
}
