<?php
namespace Paymnt\Action;

use Paymnt\GatewayInterface;

interface ActionGatewayAwareInterface extends ActionInterface
{
    function setGateway(GatewayInterface $gateway);
}