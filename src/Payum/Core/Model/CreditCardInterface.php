<?php

namespace Payum\Core\Model;

use DateTime;
use Payum\Core\Security\SensitiveValue;

interface CreditCardInterface
{
    public function getToken(): string;

    public function setToken(string $token);

    public function getBrand(): string;

    public function setBrand(string $brand);

    public function getHolder(): string;

    public function setHolder(SensitiveValue | string $holder);

    public function setMaskedHolder(string $maskedHolder);

    public function getMaskedHolder(): string;

    public function getNumber(): string;

    public function setNumber(SensitiveValue | string $number);

    public function setMaskedNumber(string $maskedNumber);

    public function getMaskedNumber(): string;

    public function getSecurityCode(): string;

    public function setSecurityCode(SensitiveValue | string $securityCode);

    public function getExpireAt(): DateTime;

    public function setExpireAt(DateTime | SensitiveValue $date = null);

    /**
     * @deprecated the method will be removed in v2
     *
     * Wraps all sensitive values by SensitiveValue objects. Prevent accidental storing of them while serialization and so on.
     */
    public function secure(): void;
}
