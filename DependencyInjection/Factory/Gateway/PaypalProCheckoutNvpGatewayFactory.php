<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class PaypalProCheckoutNvpGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'paypal_pro_checkout_nvp';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('partner')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('tender')->defaultValue('C')->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/paypal-pro-checkout-nvp';
    }
}