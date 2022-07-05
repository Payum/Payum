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
     * @return Identity
     */
    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

    public function getAfterUrl()
    {
        return $this->afterUrl;
    }

    public function setAfterUrl($afterUrl)
    {
        $this->afterUrl = $afterUrl;
    }

    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }
}
