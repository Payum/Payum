<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class CustomPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        return isset($config['service']) ?
            new DefinitionDecorator($config['service']) :
            parent::createPaymentDefinition($container, $contextName, $config)
        ;
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