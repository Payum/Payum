<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Payum\Core\Bridge\Twig\TwigFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Parameter;

class KlarnaCheckoutGatewayFactory extends AbstractGatewayFactory implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'klarna_checkout';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('merchant_id')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
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
                'PayumKlarnaCheckout' => TwigFactory::guessViewsPath('Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory'),
            )))
        ));
    }


    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        parent::load($container);

        $container->setParameter('payum.klarna_checkout.template.capture', '@PayumKlarnaCheckout/Action/capture.html.twig');
    }

    /**
     * @return array
     */
    protected function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();
        $config['payum.template.authorize'] = new Parameter('payum.klarna_checkout.template.capture');

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/klarna-checkout';
    }
}