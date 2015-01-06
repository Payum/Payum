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
    public function create(ContainerBuilder $container, $paymentName, array $config)
    {
        $payment = $this->createPaymentDefinition($container, $paymentName, $config);
        $payment->setPublic(true);
        $paymentId = 'payum.payment.'.$paymentName.'.payment';
        $container->setDefinition($paymentId, $payment);

        foreach (array_reverse($config['apis']) as $apiId) {
            $payment->addMethodCall(
                'addApi',
                array(new Reference($apiId), $forcePrepend = true)
            );
        }

        foreach (array_reverse($config['actions']) as $actionId) {
            $payment->addMethodCall(
                'addAction',
                array(new Reference($actionId), $forcePrepend = true)
            );
        }

        foreach (array_reverse($config['extensions']) as $extensionId) {
            $payment->addMethodCall(
                'addExtension',
                array(new Reference($extensionId), $forcePrepend = true)
            );
        }

        return $paymentId;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $paymentFactoryClass = $this->getPayumPaymentFactoryClass();
        if (class_exists($paymentFactoryClass)) {
            $factory = new Definition($paymentFactoryClass, array(
                new Reference('payum.payment_factory'),
            ));
            $factory->addTag('payum.payment_factory', array('name' => $this->getName()));

            $factoryId = sprintf('payum.%s.factory', $this->getName());

            $container->setDefinition($factoryId, $factory);
        }
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
     * @param $paymentName
     * @param array $config
     *
     * @return Definition
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $paymentName, array $config)
    {
        $paymentFactoryClass = $this->getPayumPaymentFactoryClass();
        if (false == class_exists($paymentFactoryClass)) {
            throw new RuntimeException(sprintf('Cannot find payment factory class. Have you installed %s or payum/payum package?', $this->getComposerPackage()));
        }

        $config['payum.factory_name'] = $this->getName();
        $config['payum.payment_name'] = $paymentName;

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService(sprintf('payum.%s.factory', $this->getName()));
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