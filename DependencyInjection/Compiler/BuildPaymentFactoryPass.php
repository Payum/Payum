<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuildPaymentFactoryPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $paymentFactory = $container->getDefinition('payum.payment_factory');

        $paymentFactory->replaceArgument(0, $container->findTaggedServiceIds('payum.action'));
        $paymentFactory->replaceArgument(1, $container->findTaggedServiceIds('payum.extension'));
        $paymentFactory->replaceArgument(2, $container->findTaggedServiceIds('payum.api'));
    }
}