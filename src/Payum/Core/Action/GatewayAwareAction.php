<?php
namespace Payum\Core\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;

abstract class GatewayAwareAction implements ActionInterface, GatewayAwareInterface
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
