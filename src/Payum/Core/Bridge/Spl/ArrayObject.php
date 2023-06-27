<?php

namespace Payum\Core\Bridge\Spl;

use ArrayAccess;
use ArrayIterator;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Security\SensitiveValue;
use ReturnTypeWillChange;
use Traversable;

class ArrayObject extends \ArrayObject
{
    protected $input;

    public function __construct($input = [], $flags = 0, $iterator_class = ArrayIterator::class)
    {
        if ($input instanceof ArrayAccess && ! $input instanceof \ArrayObject) {
            $this->input = $input;

            if (! $input instanceof Traversable) {
                throw new LogicException('Traversable interface must be implemented in case custom ArrayAccess instance given. It is because some php limitations.');
            }

            $input = iterator_to_array($input);
        }

        parent::__construct($input, $flags, $iterator_class);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $default
     */
    public function getArray(string $key, array $default = []): self
    {
        return static::ensureArrayObject($this->get($key, $default));
    }

    /**
     * @param array<string, mixed>|Traversable<string, mixed> $input
     *
     * @throws InvalidArgumentException
     */
    public function replace(iterable $input): void
    {
        foreach ($input as $index => $value) {
            $this[$index] = $value;
        }
    }

    /**
     * @param iterable<mixed> $input
     *
     * @throws InvalidArgumentException
     */
    public function defaults(iterable $input): void
    {
        foreach ($input as $index => $value) {
            if (null === $this[$index]) {
                $this[$index] = $value;
            }
        }
    }

    /**
     * @throws LogicException when one of the required fields is empty
     *
     * @param string | list<mixed> $required
     */
    public function validateNotEmpty(array | string $required, bool $throwOnInvalid = true): bool
    {
        $empty = [];

        foreach ((array) $required as $r) {
            $value = $this[$r];

            if (empty($value)) {
                $empty[] = $r;
            }
        }

        if ($empty && $throwOnInvalid) {
            throw new LogicException(sprintf('The %s fields are required.', implode(', ', $empty)));
        }

        if ($empty) {
            return false;
        }

        return true;
    }

    /**
     * @throws LogicException when one of the required fields present
     *
     * @param string | list<mixed> $required
     */
    public function validatedKeysSet(array | string $required, bool $throwOnInvalid = true): bool
    {
        foreach ((array) $required as $require) {
            if (! $this->offsetExists($require)) {
                if ($throwOnInvalid) {
                    throw new LogicException(sprintf('The %s fields is not set.', $require));
                }

                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetSet($index, $value)
    {
        if ($this->input) {
            $this->input[$index] = $value;
        }

        return parent::offsetSet($index, $value);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($index)
    {
        if ($this->input) {
            unset($this->input[$index]);
        }

        return parent::offsetUnset($index);
    }

    /**
     * This simply returns NULL when an array does not have this index.
     * It allows us not to do isset all the time we want to access something.
     *
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetGet($index)
    {
        if ($this->offsetExists($index)) {
            return parent::offsetGet($index);
        }

        return;
    }

    /**
     * @experimental
     *
     * @return mixed[]
     */
    public function toUnsafeArray(): array
    {
        $array = [];
        foreach ($this as $name => $value) {
            if ($value instanceof SensitiveValue) {
                $array[$name] = $value->get();

                continue;
            }

            $array[$name] = $value;
        }

        return $array;
    }

    /**
     * @experimental
     *
     * @return mixed[]
     */
    public function toUnsafeArrayWithoutLocal(): array
    {
        $array = $this->toUnsafeArray();
        unset($array['local']);

        return $array;
    }

    /**
     * @param mixed $input
     */
    public static function ensureArrayObject($input): self
    {
        return $input instanceof static ? $input : new static($input);
    }
}
