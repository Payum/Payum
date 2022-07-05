<?php

namespace Payum\Core;

trait GatewayAwareTrait
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}
