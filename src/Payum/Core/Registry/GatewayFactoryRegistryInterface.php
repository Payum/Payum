<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;

interface GatewayFactoryRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws InvalidArgumentException if gateway factory with such name not exist
     *
     * @return GatewayFactoryInterface
     */
    public function getGatewayFactory($name);

    /**
     * The key must be a gateway factory name
     *
     * @return GatewayFactoryInterface[]
     */
    public function getGatewayFactories();
}
