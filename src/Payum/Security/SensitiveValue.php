<?php
namespace Payum\Security;

use Payum\Exception\LogicException;

final class SensitiveValue implements \Serializable
{
    private $value;

    /**
     * @param mixed $value
     */
    public final function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    public function erase()
    {
        $this->value = null;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        throw new LogicException('It is not permitted to close this object.');
    }
}