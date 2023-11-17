<?php

namespace Payum\Core;

trait GatewayAwareTrait
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    public function setGateway(GatewayInterface $gateway): void
    {
        $this->gateway = $gateway;
    }
}
