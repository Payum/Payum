<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class PayexGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payex';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('encryption_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('account_number')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Payex\PayexGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/payex';
    }
}