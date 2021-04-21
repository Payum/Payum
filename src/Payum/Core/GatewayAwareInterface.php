<?php
namespace Payum\Core;

interface GatewayAwareInterface
{
    /**
     * @param \Payum\Core\GatewayInterface $gateway
     */
    public function setGateway(GatewayInterface $gateway);
}
