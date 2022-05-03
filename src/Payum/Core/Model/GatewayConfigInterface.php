<?php
namespace Payum\Core\Model;

interface GatewayConfigInterface
{
    public function getGatewayName(): string ;

    public function setGatewayName(string $gatewayName);

    /**
     * @deprecated since 1.3.3 will be removed in 2.0. set factory option inside the config
     */
    public function getFactoryName(): string;

    /**
     * @deprecated since 1.3.3 will be removed in 2.0. set factory option inside the config
     *
     * @param string $name
     */
    public function setFactoryName(string $name);

    public function setConfig(array $config);

    public function getConfig(): array;
}
