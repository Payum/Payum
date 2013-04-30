<?php
namespace Payum\Storage;

class Identificator implements \Serializable
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
     * @param mixed $id
     * @param string|object $class
     */
    public function __construct($id, $class)
    {
        $this->id = $id;
        $this->class = is_object($class) ? get_class($class) : $class;
    }

    /**
     * @return object|string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->id, $this->class));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->class) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->class.'#'.$this->id;
    }
}