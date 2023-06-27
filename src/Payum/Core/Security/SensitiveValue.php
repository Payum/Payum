<?php

namespace Payum\Core\Security;

use JsonSerializable;
use Payum\Core\Exception\LogicException;
use Payum\Core\Security\Util\Mask;
use ReturnTypeWillChange;
use Serializable;

final class SensitiveValue implements Serializable, JsonSerializable
{
    private mixed $value;

    final public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed[]
     */
    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
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

    public function __debugInfo()
    {
        return [
            'value' => is_scalar($this->value) ? Mask::mask($this->value) : '[FILTERED OUT]',
        ];
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

    public function serialize(): void
    {
        return;
    }

    public function unserialize($serialized): void
    {
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize(): void
    {
    }

    public static function ensureSensitive(mixed $value): self
    {
        return $value instanceof self ? $value : new self($value);
    }
}
