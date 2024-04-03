<?php

namespace Payum\Core\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class ArrayObject implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array<TKey, TValue>
     */
    protected array $details = [];

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->details);
    }

    public function offsetGet($offset): mixed
    {
        return $this->details[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->details[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->details[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->details);
    }
}
