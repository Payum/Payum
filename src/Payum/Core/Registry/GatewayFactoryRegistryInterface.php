<?php
namespace Payum\Core\Registry;

interface GatewayFactoryRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if gateway factory with such name not exist
     *
     * @return \Payum\Core\GatewayFactoryInterface
     */
    public function getGatewayFactory($name);

    /**
     * The key must be a gateway factory name
     *
     * @return \Payum\Core\GatewayFactoryInterface[]
     */
    public function getGatewayFactories();
}
