<?php
namespace Payum\Core\Security;

use Payum\Core\Exception\LogicException;
use Payum\Core\Security\Util\Mask;

final class SensitiveValue implements \Serializable, \JsonSerializable
{
    private $value;

    /**
     * @param mixed $value
     */
    final public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function peek()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $value = $this->value;

        $this->erase();

        return $value;
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
    public function jsonSerialize()
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

    /**
     * {@inheritDoc}
     */
    public function __debugInfo()
    {
        return ['value' => is_scalar($this->value) ? Mask::mask($this->value) : '[FILTERED OUT]'];
    }

    /**
     * @param mixed $value
     *
     * @return SensitiveValue
     */
    public static function ensureSensitive($value)
    {
        return $value instanceof self ? $value : new self($value);
    }
}
