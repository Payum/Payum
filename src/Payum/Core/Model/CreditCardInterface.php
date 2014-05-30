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
     * @return SensitiveValue
     */
    public function getHolder();

    /**
     * @param SensitiveValue|string $holder
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
     * @return SensitiveValue
     */
    public function getNumber();

    /**
     * @param SensitiveValue|string $number
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
     * @return SensitiveValue
     */
    public function getSecurityCode();

    /**
     * @param SensitiveValue|string $securityCode
     */
    public function setSecurityCode($securityCode);

    /**
     * @return \DateTime
     */
    public function getExpireAt();

    /**
     * @param \DateTime $date
     */
    public function setExpireAt(\DateTime $date);
}
