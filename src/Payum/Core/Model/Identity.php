<?php

namespace Payum\Core\Model;

use Payum\Core\Storage\IdentityInterface;

/**
 * @template T of object
 * @implements IdentityInterface<T>
 */
class Identity implements IdentityInterface
{
    /**
     * @var class-string<T>
     */
    protected string $class;

    protected mixed $id;

    /**
     * @param class-string<T> | T $class
     */
    public function __construct(mixed $id, string | object $class)
    {
        $this->id = $id;
        $this->class = is_object($class) ? get_class($class) : $class;
    }

    public function __serialize(): array
    {
        return [$this->id, $this->class];
    }

    public function __unserialize(array $data): void
    {
        [$this->id, $this->class] = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->class . '#' . $this->id;
    }

    /**
     * @return class-string<T>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function serialize(): ?string
    {
        return serialize([$this->id, $this->class]);
    }

    public function unserialize($serialized): void
    {
        [$this->id, $this->class] = unserialize($serialized);
    }
}
