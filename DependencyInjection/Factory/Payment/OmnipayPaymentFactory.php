<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Omnipay\Common\GatewayFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;

use Payum\Exception\RuntimeException;
use Payum\Exception\LogicException;

class OmnipayPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\Bridge\Omnipay\PaymentFactory')) {
            throw new RuntimeException('Cannot find OmnipayBridge payment factory class. Have you installed payum/omnipay-bridge package?');
        }
        if (false == interface_exists('Omnipay\Common\GatewayInterface')) {
            throw new RuntimeException('Cannot find GatewayInterface interface. Have you installed omnipay/omnipay package?');
        }

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'omnipay';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
            ->arrayNode('options')->isRequired()
                ->useAttributeAsKey('key')
                ->prototype('scalar')->end()
            ->end()
        ->end();
        
        $builder
            ->validate()
            ->ifTrue(function($v) {
                $supportedTypes = GatewayFactory::find();
                if (false == in_array($v['type'], $supportedTypes)) {
                    throw new LogicException(sprintf(
                        'Given type %s is not supported. These types %s are supported.',
                        $v['type'],
                        implode(', ', $supportedTypes)
                    ));
                }
                
                return false;
            })
            ->thenInvalid('A message')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $gatewayDefinition = new Definition();
        $gatewayDefinition->setClass('Omnipay\Common\GatewayInterface');
        $gatewayDefinition->setFactoryClass('Omnipay\Common\GatewayFactory');
        $gatewayDefinition->setFactoryMethod('create');
        $gatewayDefinition->addArgument($config['type']);
        $gatewayDefinition->setPublic(true);
        foreach ($config['options'] as $name => $value) {
            $gatewayDefinition->addMethodCall('set'.strtoupper($name), array($value));
        }

        $gatewayId = 'payum.context.'.$contextName.'.gateway';
        $container->setDefinition($gatewayId, $gatewayDefinition);

        $paymentDefinition->addMethodCall('addApi', array(new Reference($gatewayId)));
    }

    /**
     * {@inheritDoc}
     */
    protected function addActions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $captureActionDefinition = new Definition('Payum\Bridge\Omnipay\Action\CaptureAction');
        $captureActionId = 'payum.context.'.$contextName.'.action.capture';
        $container->setDefinition($captureActionId, $captureActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($captureActionId)));

        $statusActionDefinition = new Definition('Payum\Bridge\Omnipay\Action\StatusAction');
        $statusActionId = 'payum.context.'.$contextName.'.action.status';
        $container->setDefinition($statusActionId, $statusActionDefinition);
        $paymentDefinition->addMethodCall('addAction', array(new Reference($statusActionId)));
    }
}