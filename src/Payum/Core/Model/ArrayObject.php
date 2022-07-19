<?php

namespace Payum\Core\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use ReturnTypeWillChange;

class ArrayObject implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array<string, string>
     */
    protected $details = [];

    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->details);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->details[$offset];
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->details[$offset] = $value;
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->details[$offset]);
    }

    /**
     * @return ArrayIterator<string, string>
     */
    #[ReturnTypeWillChange]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->details);
    }
}
