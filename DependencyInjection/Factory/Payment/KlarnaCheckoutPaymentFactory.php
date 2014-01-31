<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Payum\Klarna\Checkout\Constants;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

class KlarnaCheckoutPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Klarna\Checkout\PaymentFactory')) {
            throw new RuntimeException('Cannot find klarna checkout payment factory class. Have you installed payum/klarna-checkout package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('klarna_checkout.xml');

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
            ->arrayNode('api')->isRequired()->children()
                ->arrayNode('options')->isRequired()->children()
                    ->scalarNode('secret')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $internalConnectorDefinition = new Definition();
        $internalConnectorDefinition->setFactoryClass('Klarna_Checkout_Connector');
        $internalConnectorDefinition->setFactoryMethod('create');
        $internalConnectorDefinition->addArgument($config['api']['options']['secret']);
        $internalConnectorId = 'payum.context.'.$contextName.'.internal_connector';
        $container->setDefinition($internalConnectorId, $internalConnectorDefinition);

        $connectorDefinition = new Definition('%payum.klarna.checkout.connector.class%');
        $connectorDefinition->addArgument(new Reference($internalConnectorId));
        $connectorDefinition->addArgument($config['api']['options']['sandbox'] ?
            Constants::BASE_URI_SANDBOX :
            Constants::BASE_URI_LIVE
        );
        $connectorDefinition->addArgument(Constants::CONTENT_TYPE_V2_PLUS_JSON);
        $connectorId = 'payum.context.'.$contextName.'.connector';
        $container->setDefinition($connectorId, $connectorDefinition);

        $paymentDefinition->addMethodCall('addApi', array(new Reference($connectorId)));
    }

    /**
     * {@inheritDoc}
     */
    protected function addActions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $captureActionDefinition = new DefinitionDecorator('payum.klarna.checkout.action.capture');
        $captureActionId = 'payum.context.'.$contextName.'.action.capture';
        $container->setDefinition($captureActionId, $captureActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureActionId)));

        $statusActionDefinition = new DefinitionDecorator('payum.klarna.checkout.action.status');
        $statusActionId = 'payum.context.'.$contextName.'.action.status';
        $container->setDefinition($statusActionId, $statusActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusActionId)));

        $createOrderActionDefinition = new DefinitionDecorator('payum.klarna.checkout.action.api.create_order');
        $createOrderActionId = 'payum.context.'.$contextName.'.action.api.create_order';
        $container->setDefinition($createOrderActionId, $createOrderActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($createOrderActionId)));

        $updateOrderActionDefinition = new DefinitionDecorator('payum.klarna.checkout.action.api.update_order');
        $updateOrderActionId = 'payum.context.'.$contextName.'.action.api.update_order';
        $container->setDefinition($updateOrderActionId, $updateOrderActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($updateOrderActionId)));
    }
}