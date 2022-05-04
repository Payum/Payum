<?php
namespace Payum\Core;

trait GatewayAwareTrait
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * {@inheritDoc}
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }
}
