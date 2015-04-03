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
     * @return string
     */
    public function getFactoryName();

    /**
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