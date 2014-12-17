<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Exception\RuntimeException;
use Payum\Klarna\Checkout\Constants;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

class KlarnaCheckoutPaymentFactory extends AbstractPaymentFactory implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Klarna\Checkout\PaymentFactory')) {
            throw new RuntimeException('Cannot find klarna checkout payment factory class. Have you installed payum/klarna-checkout package?');
        }

        return parent::create($container, $contextName, $config);
    }

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
                'PayumCore' => TwigFactory::guessViewsPath('Payum\Core\Payment'),
                'PayumKlarnaCheckout' => TwigFactory::guessViewsPath('Payum\Klarna\Checkout\PaymentFactory'),
            )))
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $klarnaConfig = new DefinitionDecorator('payum.klarna.checkout.config.prototype');
        $klarnaConfig->setProperty('merchantId', $config['merchant_id']);
        $klarnaConfig->setProperty('secret', $config['secret']);
        $klarnaConfig->setProperty('baseUri', $config['sandbox'] ?
            Constants::BASE_URI_SANDBOX :
            Constants::BASE_URI_LIVE
        );

        $klarnaConfig->setPublic(true);
        $klarnaConfigId = 'payum.context.'.$contextName.'.config';

        $container->setDefinition($klarnaConfigId, $klarnaConfig);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($klarnaConfigId)));
    }

    /**
     * {@inheritDoc}
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $klarnaConfig = new DefinitionDecorator('payum.klarna.checkout.config.prototype');
        $klarnaConfig->setProperty('merchantId', $config['merchant_id']);
        $klarnaConfig->setProperty('secret', $config['secret']);
        $klarnaConfig->setProperty('baseUri', $config['sandbox'] ?
            Constants::BASE_URI_SANDBOX :
            Constants::BASE_URI_LIVE
        );
        $klarnaConfigId = 'payum.context.'.$contextName.'.config';
        $container->setDefinition($klarnaConfigId, $klarnaConfig);

        $factoryId = 'payum.klarna_checkout.factory';
        $container->setDefinition($factoryId, new Definition('Payum\Klarna\Checkout\PaymentFactory'));

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