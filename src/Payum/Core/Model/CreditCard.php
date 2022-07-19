<?php

namespace Payum\Core\Model;

use DateTime;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Security\Util\Mask;

class CreditCard implements CreditCardInterface
{
    protected string $token;

    protected string $brand;

    protected string $holder;

    /**
     * @deprecated
     */
    protected SensitiveValue $securedHolder;

    protected string $maskedHolder;

    protected string $number;

    /**
     * @deprecated
     */
    protected SensitiveValue $securedNumber;

    protected string $maskedNumber;

    protected ?string $securityCode = null;

    /**
     * @deprecated
     */
    protected SensitiveValue $securedSecurityCode;

    protected ?DateTime $expireAt = null;

    /**
     * @deprecated
     */
    protected SensitiveValue $securedExpireAt;

    public function __construct()
    {
        $this->securedHolder = SensitiveValue::ensureSensitive(null);
        $this->securedSecurityCode = SensitiveValue::ensureSensitive(null);
        $this->securedNumber = SensitiveValue::ensureSensitive(null);
        $this->securedExpireAt = SensitiveValue::ensureSensitive(null);
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setHolder(SensitiveValue | string $holder): void
    {
        $this->securedHolder = SensitiveValue::ensureSensitive($holder);
        $this->maskedHolder = Mask::mask($this->securedHolder->peek());

        // BC
        $this->holder = $this->securedHolder->peek();
    }

    public function getHolder(): string
    {
        return $this->securedHolder->peek();
    }

    public function setMaskedHolder(string $maskedHolder): void
    {
        $this->maskedHolder = $maskedHolder;
    }

    public function getMaskedHolder(): string
    {
        return $this->maskedHolder;
    }

    public function setNumber(SensitiveValue | string $number): void
    {
        $this->securedNumber = SensitiveValue::ensureSensitive($number);
        $this->maskedNumber = Mask::mask($this->securedNumber->peek());

        //BC
        $this->number = $this->securedNumber->peek();
    }

    public function getNumber(): string
    {
        return $this->securedNumber->peek();
    }

    public function setMaskedNumber(string $maskedNumber): string
    {
        return $this->maskedNumber = $maskedNumber;
    }

    public function getMaskedNumber(): string
    {
        return $this->maskedNumber;
    }

    public function setSecurityCode(SensitiveValue | string $securityCode): void
    {
        $this->securedSecurityCode = SensitiveValue::ensureSensitive($securityCode);

        // BC
        $this->securityCode = $this->securedSecurityCode->peek();
    }

    public function getSecurityCode(): string
    {
        return $this->securedSecurityCode->peek();
    }

    public function getExpireAt(): DateTime
    {
        return $this->securedExpireAt->peek();
    }

    public function setExpireAt(DateTime | SensitiveValue $date = null): void
    {
        $date = SensitiveValue::ensureSensitive($date);

        if (! (null === $date->peek() || $date->peek() instanceof DateTime)) {
            throw new InvalidArgumentException('The date argument must be either instance of DateTime or null');
        }

        $this->securedExpireAt = $date;

        // BC
        $this->expireAt = $this->securedExpireAt->peek();
    }

    public function secure(): void
    {
        $this->holder = $this->number = $this->expireAt = $this->securityCode = null;
    }
}
