<?php
namespace Payum\Model;

use Payum\Security\TokenInterface;
use Payum\Model\Identificator;
use Payum\Security\Util\Random;

class Token implements TokenInterface
{
    /**
     * @var Identificator
     */
    protected $details;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $afterUrl;

    /**
     * @var string
     */
    protected $targetUrl;

    /**
     * @var string
     */
    protected $paymentName;

    public function __construct()
    {
        $this->hash = Random::generateToken();
    }

    /**
     * {@inheritDoc}
     * 
     * @return Identificator
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * {@inheritDoc}
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * {@inheritDoc}
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * {@inheritDoc}
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function getAfterUrl()
    {
        return $this->afterUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function setAfterUrl($afterUrl)
    {
        $this->afterUrl = $afterUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
    }
}
