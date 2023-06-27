<?php

namespace Payum\Core;

use Payum\Core\Bridge\Spl\ArrayObject;

class GatewayFactory implements GatewayFactoryInterface
{
    protected GatewayFactoryInterface $coreGatewayFactory;

    protected array $defaultConfig;

    /**
     * @param mixed[] $defaultConfig
     */
    public function __construct(array $defaultConfig = [], ?GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    public function create(array $config = []): GatewayInterface
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * @return mixed[]
     */
    public function createConfig(array $config = []): array
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array) $config));

        return (array) $config;
    }
}
