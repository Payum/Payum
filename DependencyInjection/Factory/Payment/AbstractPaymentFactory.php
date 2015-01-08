<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dump\Container;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $paymentName, array $config)
    {
        $payment = $this->createPayment($container, $paymentName, $config);
        $payment->setPublic(true);
        $paymentId = "payum.{$this->getName()}.{$paymentName}.payment";
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
        $container->setParameter('payum.template.layout', '@PayumCore\layout.html.twig');
        $container->setParameter('payum.template.obtain_credit_card', '@PayumSymfonyBridge\obtainCreditCard.html.twig');

        $paymentFactoryClass = $this->getPayumPaymentFactoryClass();
        if (class_exists($paymentFactoryClass)) {
            $factory = new Definition($paymentFactoryClass, array(
                $this->createFactoryConfig(),
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
    protected function createPayment(ContainerBuilder $container, $paymentName, array $config)
    {
        $paymentFactoryClass = $this->getPayumPaymentFactoryClass();
        if (false == class_exists($paymentFactoryClass)) {
            throw new RuntimeException(sprintf('Cannot find payment factory class. Have you installed %s or payum/payum package?', $this->getComposerPackage()));
        }

        $config['payum.payment_name'] = $paymentName;

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService(sprintf('payum.%s.factory', $this->getName()));
        $payment->setFactoryMethod('create');

        return $payment;
    }

    /**
     * @return array
     */
    protected function createFactoryConfig()
    {
        $config = array();

        $config['payum.factory_name'] = $this->getName();
        $config['payum.template.layout'] = new Parameter('payum.template.layout');
        $config['payum.template.obtain_credit_card'] = new Parameter('payum.template.obtain_credit_card');
        $config['buzz.client'] = new Reference('payum.buzz.client');
        $config['twig.env'] = new Reference('twig');

        return $config;
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

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getFactoryParameter($name)
    {
        return sprintf('payum.%s.%s', $this->getName(), $name);
    }
}