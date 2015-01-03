<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Omnipay\Omnipay;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class OmnipayDirectPaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        if (false == class_exists('Payum\OmnipayBridge\DirectPaymentFactory')) {
            throw new RuntimeException('Cannot find OmnipayBridge payment factory class. Have you installed payum/omnipay-bridge package?');
        }

        return parent::create($container, $contextName, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'omnipay_direct';
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
            ->ifTrue(function ($v) {
                $gatewayFactory = Omnipay::getFactory();
                $gatewayFactory->find();

                $supportedTypes = $gatewayFactory->all();
                if (false == in_array($v['type'], $supportedTypes) && !class_exists($v['type'])) {
                    throw new LogicException(sprintf(
                        'Given type %s is not supported. Try one of supported types: %s or use the gateway full class name.',
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
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $factoryId = 'payum.omnipay_bridge.factory';
        $container->setDefinition($factoryId, new Definition('Payum\OmnipayBridge\DirectPaymentFactory', array(
            new Reference('payum.payment_factory'),
        )));

        $config['payum.factory'] = $this->getName();
        $config['payum.context'] = $contextName;

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');

        return $payment;
    }
}
