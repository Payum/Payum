<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildGatewayFactoryPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $gatewayFactory = $container->getDefinition('payum.gateway_factory');

        $gatewayFactory->replaceArgument(0, $container->findTaggedServiceIds('payum.action'));
        $gatewayFactory->replaceArgument(1, $container->findTaggedServiceIds('payum.extension'));
        $gatewayFactory->replaceArgument(2, $container->findTaggedServiceIds('payum.api'));
    }
}