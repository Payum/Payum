<?php
namespace Payum\Core\Model;

use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\IdentityInterface;

class Token implements TokenInterface
{
    /**
     * @var IdentityInterface
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
    protected $gatewayName;

    public function __construct()
    {
        $this->hash = Random::generateToken();
    }

    /**
     * {@inheritDoc}
     *
     * @return Identity
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
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * {@inheritDoc}
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }
}
