<?php

namespace Payum\Core\Bridge\Spl;

use ArrayAccess;
use ArrayIterator;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Security\SensitiveValue;
use Traversable;

/**
 * @extends \ArrayObject<string, mixed>
 */
class ArrayObject extends \ArrayObject
{
    /**
     * @var ArrayObject<string, mixed>|ArrayAccess<string, mixed>|null
     */
    protected ArrayObject | ArrayAccess | null $input = null;

    /**
     * @param array<string, mixed>|ArrayObject<string, mixed>|ArrayAccess<string, mixed>|Traversable<string, mixed>|null $input
     */
    public function __construct($input = [], int $flags = 0, string $iterator_class = ArrayIterator::class)
    {
        if ($input instanceof ArrayAccess && ! $input instanceof \ArrayObject) {
            $this->input = $input;

            if (! $input instanceof Traversable) {
                throw new LogicException('Traversable interface must be implemented in case custom ArrayAccess instance given. It is because some php limitations.');
            }

            $input = iterator_to_array($input);
        }

        parent::__construct($input ?? [], $flags, $iterator_class);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this[$key] ?? $default;
    }

    /**
     * @param mixed|array $default
     *
     * @return ArrayObject<string, mixed>
     */
    public function getArray(string $key, mixed $default = null): self
    {
        return static::ensureArrayObject($this->get($key, $default));
    }

    /**
     * @param iterable<string, mixed> $input
     *
     * @throws InvalidArgumentException
     */
    public function replace(iterable $input): void
    {
        if (! (is_iterable($input))) {
            throw new InvalidArgumentException('Invalid input given. Should be an array or instance of \Traversable');
        }

        foreach ($input as $index => $value) {
            $this[$index] = $value;
        }
    }

    /**
     * @param iterable<string, mixed> $input
     *
     * @throws InvalidArgumentException
     */
    public function defaults(iterable $input): void
    {
        if (! (is_iterable($input))) {
            throw new InvalidArgumentException('Invalid input given. Should be an array or instance of \Traversable');
        }

        foreach ($input as $index => $value) {
            if (null === $this[$index]) {
                $this[$index] = $value;
            }
        }
    }

    /**
     * @throws LogicException when one of the required fields is empty
     */
    public function validateNotEmpty(mixed $required, bool $throwOnInvalid = true): bool
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
        return ! $empty;
    }

    /**
     * @throws LogicException when one of the required fields present
     */
    public function validatedKeysSet(mixed $required, bool $throwOnInvalid = true): bool
    {
        foreach ((array) $required as $req) {
            if (! $this->offsetExists($req)) {
                if ($throwOnInvalid) {
                    throw new LogicException(sprintf('The %s fields is not set.', $req));
                }

                return false;
            }
        }

        return true;
    }

    public function offsetSet($key, $value): void
    {
        if ($this->input) {
            $this->input[$key] = $value;
        }

        parent::offsetSet($key, $value);
    }

    public function offsetUnset($key): void
    {
        if ($this->input) {
            unset($this->input[$key]);
        }

        parent::offsetUnset($key);
    }

    /**
     * This simply returns NULL when an array does not have this index.
     * It allows us not to do isset all the time we want to access something.
     *
     * {@inheritDoc}
     */
    public function offsetGet($key): mixed
    {
        if ($this->offsetExists($key)) {
            return parent::offsetGet($key);
        }

        return null;
    }

    /**
     * @experimental
     *
     * @return array<string, mixed>
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
     * @return array<string, mixed>
     */
    public function toUnsafeArrayWithoutLocal(): array
    {
        $array = $this->toUnsafeArray();
        unset($array['local']);

        return $array;
    }

    /**
     * @param array<string, mixed>|ArrayObject<string, mixed>|\ArrayObject<string, mixed>|null $input
     * @return ArrayObject<string, mixed>
     */
    public static function ensureArrayObject(array | self | \ArrayObject | null $input): self
    {
        return $input instanceof static ? $input : new self($input);
    }
}
