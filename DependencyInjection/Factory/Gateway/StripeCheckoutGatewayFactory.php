<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Payum\Core\Bridge\Twig\TwigFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class StripeCheckoutGatewayFactory extends AbstractGatewayFactory implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'stripe_checkout';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('publishable_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('secret_key')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
            'paths' => array_flip(array_filter(array(
                'PayumCore' => TwigFactory::guessViewsPath('Payum\Core\Gateway'),
                'PayumStripe' => TwigFactory::guessViewsPath('Payum\Stripe\StripeCheckoutGatewayFactory'),
            )))
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        parent::load($container);

        $container->setParameter('payum.stripe_checkout.template.obtain_checkout_token', '@PayumStripe/Action/obtain_checkout_token.html.twig');
    }

    /**
     * {@inheritDoc}
     */
    protected function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();
        $config['payum.template.obtain_token'] = new Parameter('payum.stripe_checkout.template.obtain_checkout_token');

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Stripe\StripeCheckoutGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/stripe';
    }
}