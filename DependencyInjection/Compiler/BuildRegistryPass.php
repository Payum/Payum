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
        $registry = $container->getDefinition('payum');

        $paymentsIds = array();
        foreach ($container->findTaggedServiceIds('payum.payment') as $paymentsId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $paymentsIds[$attributes['payment']] = $paymentsId;
            }
        }

        $storagesIds = array();
        foreach ($container->findTaggedServiceIds('payum.storage') as $storageId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $storagesIds[$attributes['model_class']] = $storageId;
            }
        }

        $registry->replaceArgument(0, $paymentsIds);
        $registry->replaceArgument(1, $storagesIds);
        $registry->replaceArgument(2, ''); // todo remove default payment
    }
}