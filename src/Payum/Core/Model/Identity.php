<?php
namespace Payum\Core\Model;

use Payum\Core\Storage\IdentityInterface;

class Identity implements IdentityInterface
{
    protected string $class;

    public function __construct(protected mixed $id, string|object $class)
    {
        $this->class = is_object($class) ? get_class($class) : $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): object|string
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize(): ?string
    {
        return serialize(array($this->id, $this->class));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized): void
    {
        list($this->id, $this->class) = unserialize($serialized);
    }

    public function __serialize(): array
    {
        return array($this->id, $this->class);
    }

    public function __unserialize(array $data): void
    {
        list($this->id, $this->class) = $data;
    }

    public function __toString(): string
    {
        return $this->class.'#'.$this->id;
    }
}
