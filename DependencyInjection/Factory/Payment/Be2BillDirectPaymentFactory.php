<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Be2BillDirectPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'be2bill_direct';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('identifier')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\Be2Bill\DirectPaymentFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/be2bill';
    }
}