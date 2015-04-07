<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Omnipay\Omnipay;
use Payum\Core\Exception\LogicException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class OmnipayDirectGatewayFactory extends AbstractGatewayFactory
{
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
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\OmnipayBridge\OmnipayDirectGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/omnipay-bridge';
    }
}
