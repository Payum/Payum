<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractPaymentFactory implements PaymentFactoryInterface  
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        $paymentDefinition = new Definition();
        $paymentDefinition->setClass(new Parameter(
            $this->createContextParameter($container, $contextName, 'payum.payment.class', 'payment.class')
        ));
        $paymentDefinition->setPublic(false);
        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        foreach ($config['actions'] as $actionId) {
            $paymentDefinition->addMethodCall(
                'addAction',
                array(new Reference($actionId), $forcePrepend = true)
            );
        }

        foreach ($config['apis'] as $apiId) {
            $paymentDefinition->addMethodCall(
                'addApi',
                array(new Reference($apiId), $forcePrepend = true)
            );
        }

        foreach ($config['extensions'] as $extensionId) {
            $paymentDefinition->addMethodCall(
                'addExtension',
                array(new Reference($extensionId), $forcePrepend = true)
            );
        }

        return $paymentId;
    }

    /**
     * {@inheritdoc}
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
     * @param string $contextName
     * @param string $parameter
     * @param string $contextParameter
     * 
     * @return string
     */
    protected function createContextParameter(ContainerBuilder $container, $contextName, $parameter, $contextParameter)
    {
        $contextParameter = sprintf('payum.context.%s.%s', $contextName, $contextParameter);
        
        $container->setParameter($contextParameter, new Parameter($parameter));
        
        return $contextParameter;
    }
}