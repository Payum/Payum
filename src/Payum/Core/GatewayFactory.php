<?php

namespace Payum\Core;

use Payum\Core\Bridge\Spl\ArrayObject;

class GatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    protected $defaultConfig;

    public function __construct(array $defaultConfig = [], GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    public function create(array $config = [])
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    public function createConfig(array $config = [])
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array) $config));

        $this->populateConfig($config);

        return (array) $config;
    }

    /**
     * @return mixed|void
     */
    protected function populateConfig(ArrayObject $config)
    {
    }
}
