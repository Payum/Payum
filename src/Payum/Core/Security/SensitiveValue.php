<?php

namespace Payum\Core\Security;

use JsonSerializable;
use Payum\Core\Exception\LogicException;
use Payum\Core\Security\Util\Mask;
use ReturnTypeWillChange;
use Serializable;
use Stringable;

final class SensitiveValue implements Serializable, JsonSerializable, Stringable
{
    private $value;

    /**
     * @param mixed $value
     */
    final public function __construct($value)
    {
        $this->value = $value;
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    public function __toString(): string
    {
        return '';
    }

    public function __clone()
    {
        throw new LogicException('It is not permitted to close this object.');
    }

    public function __debugInfo()
    {
        return [
            'value' => is_scalar($this->value) ? Mask::mask($this->value) : '[FILTERED OUT]',
        ];
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

    public function erase(): void
    {
        $this->value = null;
    }

    public function serialize(): void
    {
        return;
    }

    public function unserialize($serialized): void
    {
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize(): void
    {
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
