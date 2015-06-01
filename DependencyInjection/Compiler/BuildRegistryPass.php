<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildRegistryPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('payum.static_registry');

        $gatewaysIds = array();
        foreach ($container->findTaggedServiceIds('payum.gateway') as $gatewaysId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $gatewaysIds[$attributes['gateway']] = $gatewaysId;
            }
        }

        $storagesIds = array();
        foreach ($container->findTaggedServiceIds('payum.storage') as $storageId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $storagesIds[$attributes['model_class']] = $storageId;
            }
        }

        $availableGatewayFactories = array();
        $gatewaysFactoriesIds = array();
        foreach ($container->findTaggedServiceIds('payum.gateway_factory') as $gatewayFactoryId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $gatewaysFactoriesIds[$attributes['factory_name']] = $gatewayFactoryId;

                $availableGatewayFactories[$attributes['factory_name']] = isset($attributes['human_name']) ?
                    $attributes['human_name'] :
                    $attributes['factory_name']
                ;
            }
        }

        $container->setParameter('payum.available_gateway_factories', array_replace(
            $availableGatewayFactories,
            $container->getParameter('payum.available_gateway_factories')
        ));

        $registry->replaceArgument(0, $gatewaysIds);
        $registry->replaceArgument(1, $storagesIds);
        $registry->replaceArgument(2, $gatewaysFactoriesIds);
    }
}
