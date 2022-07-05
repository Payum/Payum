<?php

namespace Payum\Core\Model;

use Payum\Core\Storage\IdentityInterface;

class Identity implements IdentityInterface
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
        $this->class = is_object($class) ? get_class($class) : $class;
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
        return serialize(array($this->id, $this->class));
    }

    public function unserialize($serialized)
    {
        list($this->id, $this->class) = unserialize($serialized);
    }

    public function __serialize(): array
    {
        return array($this->id, $this->class);
    }

    public function __unserialize(array $data)
    {
        list($this->id, $this->class) = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->class . '#' . $this->id;
    }
}
