<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        $paymentDefinition = $this->createPaymentDefinition($container, $contextName, $config);
        $paymentDefinition->setPublic(true);
        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        foreach (array_reverse($config['apis']) as $apiId) {
            $paymentDefinition->addMethodCall(
                'addApi',
                array(new Reference($apiId), $forcePrepend = true)
            );
        }

        foreach (array_reverse($config['actions']) as $actionId) {
            $paymentDefinition->addMethodCall(
                'addAction',
                array(new Reference($actionId), $forcePrepend = true)
            );
        }

        foreach (array_reverse($config['extensions']) as $extensionId) {
            $paymentDefinition->addMethodCall(
                'addExtension',
                array(new Reference($extensionId), $forcePrepend = true)
            );
        }

        return $paymentId;
    }
    
    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('actions')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('apis')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('extensions')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     *
     * @return Definition
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $paymentFactoryClass = $this->getPayumPaymentFactoryClass();
        if (false == class_exists($paymentFactoryClass)) {
            throw new RuntimeException(sprintf('Cannot find payment factory class. Have you installed %s or payum/payum package?', $this->getComposerPackage()));
        }

        $factoryId = sprintf('payum.paypal.%s.factory', $this->getName());
        $container->setDefinition($factoryId, new Definition($this->getPayumPaymentFactoryClass(), array(
            new Reference('payum.payment_factory'),
        )));

        $config['payum.factory_name'] = $this->getName();
        $config['payum.payment_name'] = $contextName;

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\Core\PaymentFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/core';
    }
}