<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PayumActionsPass implements CompilerPassInterface
{

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('payum.action') as $id => $tagAttributes) {

            foreach ($tagAttributes as $attributes) {
                $paymentIds = array();

                if (isset($attributes['all']) && $attributes['all']) {
                    $paymentIds = array_merge($paymentIds, $this->findAllPaymentIds($container));
                }

                if (isset($attributes['factory']) && $attributes['factory']) {
                    $paymentIds = array_merge(
                        $paymentIds,
                        $this->findPaymentIdsByFactory($container, $attributes['factory'])
                    );
                }
                if (isset($attributes['context']) && $attributes['context']) {
                    $paymentIds = array_merge(
                        $paymentIds,
                        $this->findPaymentIdsByContext($container, $attributes['context'])
                    );
                }

                $paymentIds = array_filter(array_unique($paymentIds));
                foreach ($paymentIds as $paymentId) {
                    $payment = $container->getDefinition($paymentId);
                    $payment->addMethodCall('addAction', array(
                        new Reference($id),
                        isset($attributes['prepend']) && $attributes['prepend']
                    ));
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $factoryName
     *
     * @return string[]
     */
    protected function findPaymentIdsByFactory(ContainerBuilder $container, $factoryName)
    {
        $paymentIds = array();
        foreach ($container->findTaggedServiceIds('payum.payment') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (isset($attributes['factory']) && $attributes['factory'] == $factoryName) {
                    $paymentIds[] = $id;
                }
            }
        }

        return $paymentIds;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $contextName
     *
     * @return string[]
     */
    protected function findPaymentIdsByContext(ContainerBuilder $container, $contextName)
    {
        $paymentIds = array();
        foreach ($container->findTaggedServiceIds('payum.payment') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (isset($attributes['context']) && $attributes['context'] == $contextName) {
                    $paymentIds[] = $id;
                }
            }
        }

        return $paymentIds;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string[]
     */
    protected function findAllPaymentIds(ContainerBuilder $container)
    {
        return array_keys($container->findTaggedServiceIds('payum.payment'));
    }
}