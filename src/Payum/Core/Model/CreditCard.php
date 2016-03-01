<?php
namespace Payum\Core\Model;

use Payum\Core\Exception\InvalidArgumentException;
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
     * @var string
     */
    protected $holder;

    /**
     * @var SensitiveValue
     *
     * @deprecated
     */
    protected $securedHolder;

    /**
     * @var string
     */
    protected $maskedHolder;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var SensitiveValue
     *
     * @deprecated
     */
    protected $securedNumber;

    /**
     * @var string
     */
    protected $maskedNumber;

    /**
     * @var string
     */
    protected $securityCode;

    /**
     * @var SensitiveValue
     *
     * @deprecated
     */
    protected $securedSecurityCode;

    /**
     * @var \DateTime
     */
    protected $expireAt;

    /**
     * @var SensitiveValue
     *
     * @deprecated
     */
    protected $securedExpireAt;

    public function __construct()
    {
        $this->holder = SensitiveValue::ensureSensitive(null);
        $this->securityCode = SensitiveValue::ensureSensitive(null);
        $this->number = SensitiveValue::ensureSensitive(null);
        $this->expireAt = SensitiveValue::ensureSensitive(null);
    }

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
    public function setHolder($holder)
    {
        $this->holder = SensitiveValue::ensureSensitive($holder);
        $this->maskedHolder = Mask::mask($this->holder->peek());

        // BC
        $this->securedHolder = $this->holder;
    }

    /**
     * {@inheritDoc}
     */
    public function getHolder()
    {
        return $this->holder->peek();
    }

    /**
     * {@inheritDoc}
     */
    public function setMaskedHolder($maskedHolder)
    {
        $this->maskedHolder = $maskedHolder;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaskedHolder()
    {
        return $this->maskedHolder;
    }

    /**
     * {@inheritDoc}
     */
    public function setNumber($number)
    {
        $this->number = SensitiveValue::ensureSensitive($number);
        $this->maskedNumber = Mask::mask($this->number->peek());

        //BC
        $this->securedNumber = $this->number;
    }

    /**
     * {@inheritDoc}
     */
    public function getNumber()
    {
        return $this->number->peek();
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
        $this->securityCode = SensitiveValue::ensureSensitive($securityCode);

        // BC
        $this->securedSecurityCode = $this->securityCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityCode()
    {
        return $this->securityCode->peek();
    }

    /**
     * {@inheritDoc}
     */
    public function getExpireAt()
    {
        return $this->expireAt->peek();
    }

    /**
     * {@inheritDoc}
     */
    public function setExpireAt($date = null)
    {
        $date = SensitiveValue::ensureSensitive($date);

        if (false == (null === $date->peek() || $date->peek() instanceof \DateTime)) {
            throw new InvalidArgumentException('The date argument must be either instance of DateTime or null');
        }

        $this->expireAt = $date;

        // BC
        $this->securedExpireAt = $this->expireAt;
    }

    /**
     * {@inheritDoc}
     */
    public function secure()
    {
    }
}
