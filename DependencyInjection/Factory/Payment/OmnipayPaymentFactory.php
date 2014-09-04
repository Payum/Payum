<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Omnipay\Omnipay;
use Omnipay\Common\GatewayFactory;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Exception\LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class OmnipayPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\OmnipayBridge\PaymentFactory')) {
            throw new RuntimeException('Cannot find OmnipayBridge payment factory class. Have you installed payum/omnipay-bridge package?');
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/payment'));
        $loader->load('omnipay_bridge.xml');

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
                $gatewayFactory = Omnipay::getFactory();
                $gatewayFactory->find();

                $supportedTypes = $gatewayFactory->all();
                if (false == in_array($v['type'], $supportedTypes)) {
                    throw new LogicException(sprintf(
                        'Given type %s is not supported. Try one of supported types: %s.',
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
        $gatewayDefinition->setFactoryService('payum.omnipay_bridge.gateway_factory');
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
}
