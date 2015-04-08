<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class KlarnaInvoiceGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $gatewayName, array $config)
    {
        if (false == class_exists('Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory')) {
            throw new RuntimeException('Cannot find Klarna Invoice gateway factory class. Have you installed payum/klarna-invoice package?');
        }

        //autoload Klarna
        \Klarna::BETA;

        return parent::create($container, $gatewayName, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'klarna_invoice';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('eid')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('country')->defaultValue('SE')->cannotBeEmpty()->end()
            ->scalarNode('language')->defaultValue('SV')->cannotBeEmpty()->end()
            ->scalarNode('currency')->defaultValue('SEK')->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/klarna-invoice';
    }
}