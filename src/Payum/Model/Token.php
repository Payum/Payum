<?php
namespace Payum\Model;

use Payum\Exception\InvalidArgumentException;
use Payum\Security\TokenInterface;
use Payum\Storage\Identificator;
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
     *
     * @param Identificator $details
     * 
     * @throws InvalidArgumentException if $details is not instance of Identificator
     *
     * @return void
     */
    public function setDetails($details)
    {
        if (false == $details instanceof Identificator) {
            throw new InvalidArgumentException('Details must be instance of `Identificator`.');
        }

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