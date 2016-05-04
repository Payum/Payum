<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayInterface;

interface GatewayRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws InvalidArgumentException if gateway with such name not exist
     *
     * @return GatewayInterface
     */
    public function getGateway($name);

    /**
     * The key must be a gateway name
     *
     * @return GatewayInterface[]
     */
    public function getGateways();
}
