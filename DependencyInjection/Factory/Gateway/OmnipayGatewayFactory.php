<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Omnipay\Common\GatewayFactory;
use Omnipay\Omnipay;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OmnipayGatewayFactory extends AbstractGatewayFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'omnipay';
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $omnipayFactory = new Definition(GatewayFactory::class);
        $omnipayFactory->setFactory([Omnipay::class, 'getFactory']);

        $container->setDefinition('payum.omnipay_factory', $omnipayFactory);

        parent::load($container);
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
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     */
    protected function createGatewayFactory(ContainerBuilder $container)
    {
        $factory = new Definition($this->getPayumGatewayFactoryClass(), array(
            null,
            new Reference('payum.omnipay_factory'),
            $this->createFactoryConfig(),
            new Reference('payum.gateway_factory')
        ));
        $factory->addTag('payum.gateway_factory', array(
            'factory_name' => $this->getName(),
            'human_name' => $this->getHumanName(),
        ));

        $factoryId = sprintf('payum.%s.factory', $this->getName());

        $container->setDefinition($factoryId, $factory);

        return $factoryId;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return \Payum\OmnipayBridge\OmnipayGatewayFactory::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/omnipay-bridge';
    }
}
