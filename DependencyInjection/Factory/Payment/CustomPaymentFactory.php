<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class CustomPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        if (isset($config['service'])) {
            return new DefinitionDecorator($config['service']);
        }

        $config['payum.factory'] = $this->getName();
        $config['payum.context'] = $contextName;

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService('payum.payment_factory');
        $payment->setFactoryMethod('create');

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'custom';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
            ->scalarNode('service')->defaultValue(null)->end()
        ->end();
    }
}