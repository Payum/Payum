<?php
namespace Payum\Stripe;

class Keys
{
    /**
     * @var string
     */
    protected $publishable;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @param string $publishable
     * @param string $secret
     */
    public function __construct($publishable, $secret)
    {
        $this->publishable = $publishable;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->publishable;
    }
}
