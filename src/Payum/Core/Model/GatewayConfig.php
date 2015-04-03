<?php
namespace Payum\Core\Model;

class GatewayConfig implements GatewayConfigInterface
{
    /**
     * @var string
     */
    protected $factoryName;

    /**
     * @var string
     */
    protected $gatewayName;

    /**
     * @var array
     */
    protected $config;

    public function __construct()
    {
        $this->config = array();
    }

    /**
     * {@inheritDoc}
     */
    public function getFactoryName()
    {
        return $this->factoryName;
    }

    /**
     * {@inheritDoc}
     */
    public function setFactoryName($factoryName)
    {
        $this->factoryName = $factoryName;
    }

    /**
     * @return string
     */
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}