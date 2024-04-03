<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayInterface;

interface GatewayRegistryInterface
{
    /**
     * @throws InvalidArgumentException if gateway with such name not exist
     */
    public function getGateway(string $name): GatewayInterface;

    /**
     * The key must be a gateway name
     *
     * @return array<string, GatewayInterface>
     */
    public function getGateways(): array;
}
