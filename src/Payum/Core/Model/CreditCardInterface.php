<?php
namespace Payum\Core\Model;

use Payum\Core\Security\SensitiveValue;

interface CreditCardInterface
{
    /**
     * @return string
     */
    public function getToken();

    /**
     * @param string $token
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getBrand();

    /**
     * @param string $brand
     */
    public function setBrand($brand);

    /**
     * @return string
     */
    public function getHolder();

    /**
     * @param string|SensitiveValue $holder
     */
    public function setHolder($holder);

    /**
     * @param string $maskedHolder
     */
    public function setMaskedHolder($maskedHolder);

    /**
     * @return string
     */
    public function getMaskedHolder();

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @param string|SensitiveValue $number
     */
    public function setNumber($number);

    /**
     * @param string $maskedNumber
     */
    public function setMaskedNumber($maskedNumber);

    /**
     * @return string
     */
    public function getMaskedNumber();

    /**
     * @return string
     */
    public function getSecurityCode();

    /**
     * @param string|SensitiveValue $securityCode
     */
    public function setSecurityCode($securityCode);

    /**
     * @return \DateTime
     */
    public function getExpireAt();

    /**
     * @param \DateTime|SensitiveValue $date
     */
    public function setExpireAt($date = null);

    /**
     * @deprecated the method will be removed in v2
     *
     * Wraps all sensitive values by SensitiveValue objects. Prevent accidental storing of them while serialization and so on.
     */
    public function secure();
}
