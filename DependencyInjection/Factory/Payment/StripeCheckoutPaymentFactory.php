<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

class StripeCheckoutPaymentFactory extends AbstractPaymentFactory implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Stripe\CheckoutPaymentFactory')) {
            throw new RuntimeException('Cannot find stripe payment factory class. Have you installed payum/stripe package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('stripe.xml');

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'stripe_checkout';
    }

    /**
     * {@inheritdoc}
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
                'PayumCore' => TwigFactory::guessViewsPath('Payum\Core\Payment'),
                'PayumStripe' => TwigFactory::guessViewsPath('Payum\Stripe\CheckoutPaymentFactory'),
            )))
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $publishableKey = new DefinitionDecorator('payum.stripe.keys.prototype');
        $publishableKey->replaceArgument(0, $config['publishable_key']);
        $publishableKey->replaceArgument(1, $config['secret_key']);
        $publishableKey->setPublic(true);
        $publishableKeyId = 'payum.context.'.$contextName.'.keys';

        $container->setDefinition($publishableKeyId, $publishableKey);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($publishableKeyId)));
    }
}