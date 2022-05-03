<?php
namespace Payum\Core;

trait GatewayAwareTrait
{
    protected GatewayInterface $gateway;

    /**
     * {@inheritDoc}
     */
    public function setGateway(GatewayInterface $gateway): void
    {
        $this->gateway = $gateway;
    }
}
