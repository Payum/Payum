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

    public function getSecretKey(): string
    {
        return $this->secret;
    }

    public function getPublishableKey(): string
    {
        return $this->publishable;
    }
}
