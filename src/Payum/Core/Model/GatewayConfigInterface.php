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
     * @deprecated since 1.3.3 will be removed in 2.0. set factory option inside the config
     *
     * @return string
     */
    public function getFactoryName();

    /**
     * @deprecated since 1.3.3 will be removed in 2.0. set factory option inside the config
     *
     * @param string $name
     */
    public function setFactoryName($name);

    /**
     * @param array $config
     */
    public function setConfig(array $config);

    /**
     * @return array
     */
    public function getConfig();
}
