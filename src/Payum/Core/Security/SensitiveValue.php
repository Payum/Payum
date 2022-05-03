<?php
namespace Payum\Core\Security;

use Payum\Core\Exception\LogicException;
use Payum\Core\Security\Util\Mask;

final class SensitiveValue implements \Serializable, \JsonSerializable
{
    private mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public function peek(): mixed
    {
        return $this->value;
    }

    public function get(): mixed
    {
        $value = $this->value;

        $this->erase();

        return $value;
    }

    public function erase(): void
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
    public function unserialize($serialized): void
    {
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
    }

    public function __toString()
    {
        return '';
    }

    public function __clone()
    {
        throw new LogicException('It is not permitted to close this object.');
    }

    public function __debugInfo(): array
    {
        return ['value' => is_scalar($this->value) ? Mask::mask($this->value) : '[FILTERED OUT]'];
    }

    public static function ensureSensitive(mixed $value): SensitiveValue
    {
        return $value instanceof self ? $value : new self($value);
    }
}
