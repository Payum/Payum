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

        $availablePaymentFactories = array();
        $paymentsFactoriesIds = array();
        foreach ($container->findTaggedServiceIds('payum.payment_factory') as $paymentFactoryId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $paymentsFactoriesIds[$attributes['name']] = $paymentFactoryId;

                $availablePaymentFactories[$attributes['name']] = isset($attributes['human_name']) ?
                    $attributes['human_name'] :
                    $attributes['name']
                ;
            }
        }

        $container->setParameter('payum.available_payment_factories', array_replace(
            $availablePaymentFactories,
            $container->getParameter('payum.available_payment_factories')
        ));

        $registry->replaceArgument(0, $paymentsIds);
        $registry->replaceArgument(1, $storagesIds);
        $registry->replaceArgument(2, $paymentsFactoriesIds);
    }
}