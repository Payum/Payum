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
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     *
     * @return Definition
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $container->setParameter('payum.stripe.template.obtain_checkout_token', '@PayumStripe/Action/obtain_checkout_token.html.twig');

        $keys = new Definition('Payum\Stripe\Keys', array(
            $config['publishable_key'],
            $config['secret_key']
        ));
        $container->setDefinition('payum.context.'.$contextName.'.keys', $keys);

        $factoryId = 'payum.stripe.checkout_factory';
        $container->setDefinition($factoryId, new Definition('Payum\Stripe\CheckoutPaymentFactory'));

        $config['payum.template.obtain_token'] = '%payum.stripe.template.obtain_checkout_token%';
        $config['buzz.client'] = new Reference('payum.buzz.client');
        $config['twig.env'] = new Reference('twig');
        $config['payum.action.get_http_request'] = new Reference('payum.action.get_http_request');
        $config['payum.action.obtain_credit_card'] = new Reference('payum.action.obtain_credit_card');
        $config['payum.extension.log_executed_actions'] = new Reference('payum.extension.log_executed_actions');
        $config['payum.extension.logger'] = new Reference('payum.extension.logger');

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');

        return $payment;
    }
}