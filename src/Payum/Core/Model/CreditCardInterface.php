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
    public function getCardHolder();

    /**
     * @param SensitiveValue|string $cardHolder
     */
    public function setCardHolder($cardHolder);

    /**
     * @param string $maskedCardHolder
     */
    public function setMaskedCardHolder($maskedCardHolder);

    /**
     * @return string
     */
    public function getMaskedCardHolder();

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
     * @return SensitiveValue
     */
    public function getExpiryMonth();

    /**
     * @param SensitiveValue|integer
     */
    public function setExpiryMonth($expiryMonth);

    /**
     * @return SensitiveValue
     */
    public function getExpiryYear();

    /**
     * @param SensitiveValue|integer $expiryYear
     */
    public function setExpiryYear($expiryYear);
}
