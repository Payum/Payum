<?php
namespace Payum\Core\Model;

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
     * @param string $holder
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
     * @param string $number
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
     * @param string $securityCode
     */
    public function setSecurityCode($securityCode);

    /**
     * @return \DateTime
     */
    public function getExpireAt();

    /**
     * @param \DateTime $date
     */
    public function setExpireAt(\DateTime $date = null);

    /**
     * Wraps all sensitive values by SensitiveValue objects. Prevent accidental storing of them while serialization and so on.
     */
    public function secure();
}
