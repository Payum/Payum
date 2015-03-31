<?php
namespace Payum\Core\Registry;

interface GatewayRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if gateway with such name not exist
     *
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway($name);

    /**
     * The key must be a gateway name
     *
     * @return \Payum\Core\GatewayInterface[]
     */
    public function getGateways();
}
