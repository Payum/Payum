<?php

namespace Payum\Core\Model;

use Payum\Core\Storage\IdentityInterface;
use Stringable;

class Identity implements IdentityInterface, Stringable
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @param mixed         $id
     * @param string|object $class
     */
    public function __construct($id, $class)
    {
        $this->id = $id;
        $this->class = is_object($class) ? $class::class : $class;
    }

    public function __serialize(): array
    {
        return [$this->id, $this->class];
    }

    public function __unserialize(array $data): void
    {
        [$this->id, $this->class] = $data;
    }

    public function __toString(): string
    {
        return $this->class . '#' . $this->id;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getId()
    {
        return $this->id;
    }

    public function serialize()
    {
        return serialize([$this->id, $this->class]);
    }

    public function unserialize($serialized): void
    {
        [$this->id, $this->class] = unserialize($serialized);
    }
}
