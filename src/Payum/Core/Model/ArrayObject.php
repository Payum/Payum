<?php
namespace Payum\Core\Model;

class ArrayObject implements \ArrayAccess, \IteratorAggregate
{
    protected array $details = [];

    /**
     * {@inheritDoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->details);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->details[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->details[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->details[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable|array|\ArrayIterator
    {
        return new \ArrayIterator($this->details);
    }
}
