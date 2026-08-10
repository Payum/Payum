<?php

namespace Payum\Core\Metadata\Logo;

use Payum\Core\Metadata\Logo;

class Path implements Logo
{
    public function __construct(
        private readonly string $value
    ) {
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
