<?php

namespace Invit\PayumSofort\Bridge;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class SymfonySofortGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sofort';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
                ->scalarNode('config_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('abort_url')->isRequired()->cannotBeEmpty()->end()
                ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Invit\PayumSofort\SofortGatewayFactory';
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackage()
    {
        return 'invit/payum-sofort';
    }
}
