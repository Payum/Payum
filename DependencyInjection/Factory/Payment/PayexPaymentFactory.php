<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

use Payum\Exception\RuntimeException;

class PayexPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Payex\PaymentFactory')) {
            throw new RuntimeException('Cannot find payex payment factory class. Have you installed payum/payex package?');
        }

        $paymentId = parent::create($container, $contextName, $config);
        $paymentDefinition = $container->getDefinition($paymentId);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('payex.xml');

        $orderApiDefinition = new DefinitionDecorator('payum.payex.api.order.prototype');
        $orderApiDefinition->replaceArgument(1, array(
            'encryptionKey' => $config['api']['options']['encryption_key'],
            'accountNumber' => $config['api']['options']['account_number'],
            'sandbox' => $config['api']['options']['sandbox']
        ));
        $orderApiDefinition->setPublic(true);
        $orderApiId = 'payum.context.'.$contextName.'.api.order';
        $container->setDefinition($orderApiId, $orderApiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($orderApiId)));

        $initializeOrderActionDefinition = new DefinitionDecorator('payum.payex.action.api.initialize_order');
        $initializeOrderActionId = 'payum.context.'.$contextName.'.action.api.initialize_order';
        $container->setDefinition($initializeOrderActionId, $initializeOrderActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($initializeOrderActionId)));

        $completeOrderActionDefinition = new DefinitionDecorator('payum.payex.action.api.complete_order');
        $completeOrderActionId = 'payum.context.'.$contextName.'.action.api.complete_order';
        $container->setDefinition($completeOrderActionId, $completeOrderActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($completeOrderActionId)));
        
        $captureActionDefinition = new DefinitionDecorator('payum.payex.action.capture');
        $captureActionId = 'payum.context.'.$contextName.'.action.capture';
        $container->setDefinition($captureActionId, $captureActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureActionId)));

        $statusActionDefinition = new DefinitionDecorator('payum.payex.action.status');
        $statusActionId = 'payum.context.'.$contextName.'.action.status';
        $container->setDefinition($statusActionId, $statusActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusActionId)));
//
        return $paymentId;
    }

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
            ->arrayNode('api')->isRequired()->children()
                ->arrayNode('options')->isRequired()->children()
                    ->scalarNode('encryption_key')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('account_number')->isRequired()->cannotBeEmpty()->end()
                    ->booleanNode('sandbox')->defaultTrue()->end()
                ->end()
            ->end()
        ->end();
    }
}