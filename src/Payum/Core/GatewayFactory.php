<?php

namespace Payum\Core;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Legacy\LegacyFactoryScope;
use function trigger_deprecation;

class GatewayFactory implements GatewayFactoryInterface /* , ContainerConfiguration */ // This class will implement ContainerConfiguration from version 3.0, and will remove the GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    protected $defaultConfig;

    public function __construct(array $defaultConfig = [], ?GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    public function create(array $config = [])
    {
        $this->reportLegacyFactory();

        return LegacyFactoryScope::run(fn () => $this->coreGatewayFactory->create($this->createConfig($config)));
    }

    public function createConfig(array $config = [])
    {
        $this->reportLegacyFactory();

        return LegacyFactoryScope::run(fn (): array => $this->buildConfig($config));
    }

    /**
     * @return mixed|void
     */
    protected function populateConfig(ArrayObject $config)
    {
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private function buildConfig(array $config): array
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array) $config));

        $this->populateConfig($config);

        return (array) $config;
    }

    private function reportLegacyFactory(): void
    {
        if (! LegacyFactoryScope::isActive()) {
            trigger_deprecation('payum/core', '2.0.0', '%s is a 1.x gateway factory, which is deprecated and will not be supported in 3.0. It needs to implement %s.', static::class, ContainerConfiguration::class);
        }
    }
}
