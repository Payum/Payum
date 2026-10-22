<?php

namespace Payum\Core\Model;

interface GatewayConfigInterface
{
    /**
     * @return string
     */
    public function getGatewayName();

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName);

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Set the 'factory' option inside the config
     *             array instead, which setConfig() takes.
     *
     * @return string
     */
    public function getFactoryName();

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Set the 'factory' option inside the config
     *             array instead, which setConfig() takes.
     *
     * @param string $name
     */
    public function setFactoryName($name);

    public function setConfig(array $config);

    /**
     * @return array
     */
    public function getConfig();
}
