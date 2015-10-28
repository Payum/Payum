<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractGatewayFactory implements GatewayFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $gatewayName, array $config)
    {
        $gateway = $this->createGateway($container, $gatewayName, $config);
        $gateway->setPublic(true);
        $gatewayId = "payum.{$this->getName()}.{$gatewayName}.gateway";
        $container->setDefinition($gatewayId, $gateway);

        foreach (array_reverse($config['apis']) as $apiId) {
            $gateway->addMethodCall(
                'addApi',
                array(new Reference($apiId), $forcePrepend = true)
            );
        }

        foreach (array_reverse($config['actions']) as $actionId) {
            $gateway->addMethodCall(
                'addAction',
                array(new Reference($actionId), $forcePrepend = true)
            );
        }

        foreach (array_reverse($config['extensions']) as $extensionId) {
            $gateway->addMethodCall(
                'addExtension',
                array(new Reference($extensionId), $forcePrepend = true)
            );
        }

        return $gatewayId;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->setParameter('payum.template.layout', '@PayumCore\layout.html.twig');
        $container->setParameter('payum.template.obtain_credit_card', '@PayumSymfonyBridge\obtainCreditCard.html.twig');

        $gatewayFactoryClass = $this->getPayumGatewayFactoryClass();
        if (class_exists($gatewayFactoryClass)) {
            $this->createGatewayFactory($container);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('actions')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('apis')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('extensions')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param $gatewayName
     * @param array $config
     *
     * @return Definition
     */
    protected function createGateway(ContainerBuilder $container, $gatewayName, array $config)
    {
        $gatewayFactoryClass = $this->getPayumGatewayFactoryClass();
        if (false == class_exists($gatewayFactoryClass)) {
            throw new RuntimeException(sprintf('Cannot find gateway factory class. Have you installed %s or payum/payum package?', $this->getComposerPackage()));
        }

        $config['payum.gateway_name'] = $gatewayName;


        $gateway = new Definition('Payum\Core\Gateway', array($config));
        $gateway->setFactory(array(
            new Reference(sprintf('payum.%s.factory', $this->getName())),
            'create'
        ));

        return $gateway;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     */
    protected function createGatewayFactory(ContainerBuilder $container)
    {
        $factory = new Definition($this->getPayumGatewayFactoryClass(), array(
            $this->createFactoryConfig(),
            new Reference('payum.gateway_factory'),
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
     * @return array
     */
    protected function createFactoryConfig()
    {
        $config = array();

        $config['payum.factory_name'] = $this->getName();
        $config['payum.template.layout'] = new Parameter('payum.template.layout');
        $config['payum.template.obtain_credit_card'] = new Parameter('payum.template.obtain_credit_card');
        $config['payum.http_client'] = new Reference('payum.http_client');
        $config['payum.security.token_storage'] = new Reference('payum.security.token_storage');
        $config['guzzle.client'] = new Reference('payum.guzzle_client');
        $config['twig.env'] = new Reference('twig');
        $config['payum.iso4217'] = new Reference('payum.iso4217');

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Core\GatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/core';
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getFactoryParameter($name)
    {
        return sprintf('payum.%s.%s', $this->getName(), $name);
    }

    /**
     * @return string
     */
    protected function getHumanName()
    {
        return ucwords(str_replace(array('-', '_'), ' ', $this->getName()));
    }
}