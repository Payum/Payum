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
     * @var string
     */
    protected $holder;

    /**
     * @var SensitiveValue
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
     */
    protected $securedSecurityCode;

    /**
     * @var \DateTime
     */
    protected $expireAt;

    /**
     * @var SensitiveValue
     */
    protected $securedExpireAt;

    public function __construct()
    {
        $this->holder = new SensitiveValue(null);
        $this->securityCode = new SensitiveValue(null);
        $this->number = new SensitiveValue(null);
        $this->expireAt = new SensitiveValue(null);
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
        $this->holder = $holder;
        $this->maskedHolder = Mask::mask($this->holder);
    }

    /**
     * {@inheritDoc}
     */
    public function getHolder()
    {
        return $this->securedHolder ? $this->securedHolder->peek() : $this->holder;
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
        $this->number = $number;
        $this->maskedNumber = Mask::mask($this->number);
    }

    /**
     * {@inheritDoc}
     */
    public function getNumber()
    {
        return $this->securedNumber ? $this->securedNumber->peek() : $this->number;
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
        $this->securityCode = $securityCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecurityCode()
    {
        return $this->securedSecurityCode ? $this->securedSecurityCode->peek() : $this->securityCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpireAt()
    {
        return $this->securedExpireAt ? $this->securedExpireAt->peek() : $this->expireAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setExpireAt(\DateTime $date = null)
    {
        $this->expireAt = $date;
    }

    /**
     * {@inheritDoc}
     */
    public function secure()
    {
        if ($this->holder) {
            $this->securedHolder = new SensitiveValue($this->holder);
            $this->holder = null;
        }

        if ($this->number) {
            $this->securedNumber = new SensitiveValue($this->number);
            $this->number = null;
        }

        if ($this->securityCode) {
            $this->securedSecurityCode = new SensitiveValue($this->securityCode);
            $this->securityCode = null;
        }

        if ($this->expireAt) {
            $this->securedExpireAt = new SensitiveValue($this->expireAt);
            $this->expireAt = null;
        }
    }
}
