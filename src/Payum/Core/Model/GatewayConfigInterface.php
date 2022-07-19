<?php

namespace Payum\Core\Model;

interface GatewayConfigInterface
{
    /**
     * @return string
     */
    public function getGatewayName();

    public function setGatewayName(string $gatewayName);

    /**
     * @deprecated since 1.3.3 will be removed in 2.0. set factory option inside the config
     *
     * @return string
     */
    public function getFactoryName();

    /**
     * @deprecated since 1.3.3 will be removed in 2.0. set factory option inside the config
     */
    public function setFactoryName(string $name);

    public function setConfig(array $config);

    /**
     * @return array
     */
    public function getConfig();
}
