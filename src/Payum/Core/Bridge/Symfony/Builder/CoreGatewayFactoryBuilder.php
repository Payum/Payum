<?php
namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\ContainerAwareCoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CoreGatewayFactoryBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param array $defaultConfig
     *
     * @return GatewayFactoryInterface
     */
    public function build(array $defaultConfig)
    {
        $coreGatewayFactory = new ContainerAwareCoreGatewayFactory($defaultConfig);
        $coreGatewayFactory->setContainer($this->container);

        return $coreGatewayFactory;
    }
}