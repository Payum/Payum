<?php
namespace Payum\Core\Model;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Security\Util\Mask;

class CreditCard implements CreditCardInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $brand;

    /**
     * @var SensitiveValue
     */
    protected $cardHolder;

    /**
     * @var string
     */
    protected $maskedCardHolder;

    /**
     * @var SensitiveValue
     */
    protected $number;

    /**
     * @var string
     */
    protected $maskedNumber;

    /**
     * @var SensitiveValue
     */
    protected $securityCode;

    /**
     * @var SensitiveValue
     */
    protected $expiryMonth;

    /**
     * @var SensitiveValue
     */
    protected $expiryYear;

    /**
     * {@inheritDoc}
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritDoc}
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritDoc}
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * {@inheritDoc}
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * {@inheritDoc}
     */
    public function setCardHolder($cardHolder)
    {
        $this->cardHolder = $this->wrapBySensitiveValue($cardHolder);

        $this->maskedCardHolder = Mask::mask($this->cardHolder->peek());
    }

    /**
     * {@inheritDoc}
     */
    public function getCardHolder()
    {
        return $this->cardHolder;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaskedCardHolder($maskedCardHolder)
    {
        $this->maskedCardHolder = $maskedCardHolder;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaskedCardHolder()
    {
        return $this->maskedCardHolder;
    }

    /**
     * {@inheritDoc}
     */
    public function setNumber($number)
    {
        $this->number = $this->wrapBySensitiveValue($number);

        $this->maskedNumber = Mask::mask($this->number->peek());
    }

    /**
     * {@inheritDoc}
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaskedNumber($maskedNumber)
    {
        return $this->maskedNumber = $maskedNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaskedNumber()
    {
        return $this->maskedNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function setSecurityCode($securityCode)
    {
        $this->securityCode = $this->wrapBySensitiveValue($securityCode);
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityCode()
    {
        return $this->securityCode;
    }

    /**
     * {@inheritDoc}
     */
    public function setExpiryMonth($expiryMonth)
    {
        $this->expiryMonth = $this->wrapBySensitiveValue($expiryMonth);
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiryMonth()
    {
        return $this->expiryMonth;
    }

    /**
     * {@inheritDoc}
     */
    public function setExpiryYear($expiryYear)
    {
        $this->expiryYear = $this->wrapBySensitiveValue($expiryYear);
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiryYear()
    {
        return $this->expiryYear;
    }

    /**
     * @param mixed $value
     *
     * @return SensitiveValue
     */
    protected function wrapBySensitiveValue($value)
    {
        return $value instanceof SensitiveValue ? $value : new SensitiveValue($value);
    }
}

