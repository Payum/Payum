<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;

interface GatewayFactoryRegistryInterface
{
    /**
     * @throws InvalidArgumentException if gateway factory with such name not exist
     */
    public function getGatewayFactory(string $name): GatewayFactoryInterface;

    /**
     * The key must be a gateway factory name
     *
     * @return GatewayFactoryInterface[]
     */
    public function getGatewayFactories(): array;
}
