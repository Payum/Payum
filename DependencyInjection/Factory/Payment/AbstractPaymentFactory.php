<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractPaymentFactory implements PaymentFactoryInterface  
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        $paymentDefinition = $this->createPaymentDefinition($container, $contextName, $config);
        $paymentDefinition->setPublic(true);
        $paymentId = 'payum.context.'.$contextName.'.payment';
        $container->setDefinition($paymentId, $paymentDefinition);

        $this->addCommonApis($paymentDefinition, $container, $contextName, $config);
        $this->addApis($paymentDefinition, $container, $contextName, $config);
        $this->addCustomApis($paymentDefinition, $container, $contextName, $config);

        $this->addCommonActions($paymentDefinition, $container, $contextName, $config);
        $this->addActions($paymentDefinition, $container, $contextName, $config);
        $this->addCustomActions($paymentDefinition, $container, $contextName, $config);

        $this->addCommonExtensions($paymentDefinition, $container, $contextName, $config);
        $this->addExtensions($paymentDefinition, $container, $contextName, $config);
        $this->addCustomExtensions($paymentDefinition, $container, $contextName, $config);

        return $paymentId;
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
     * @param $contextName
     * @param array $config
     * 
     * @return Definition
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $paymentDefinition = new Definition();
        $paymentDefinition->setClass(
            $this->createContextParameter($container, $contextName, '%payum.payment.class%', 'payment.class')
        );
        
        return $paymentDefinition;
    }
    

    /**
     * @param ContainerBuilder $container
     * @param string $contextName
     * @param string $parameter
     * @param string $contextParameter
     * 
     * @return string
     */
    protected function createContextParameter(ContainerBuilder $container, $contextName, $parameter, $contextParameter)
    {
        $contextParameter = sprintf('payum.context.%s.%s', $contextName, $contextParameter);
        
        $container->setParameter($contextParameter, $parameter);
        
        return "%{$contextParameter}%";
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addCustomApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        foreach (array_reverse($config['apis']) as $apiId) {
            $paymentDefinition->addMethodCall(
                'addApi',
                array(new Reference($apiId), $forcePrepend = true)
            );
        }
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addCustomActions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        foreach (array_reverse($config['actions']) as $actionId) {
            $paymentDefinition->addMethodCall(
                'addAction',
                array(new Reference($actionId), $forcePrepend = true)
            );
        }
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addCustomExtensions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        foreach (array_reverse($config['extensions']) as $extensionId) {
            $paymentDefinition->addMethodCall(
                'addExtension',
                array(new Reference($extensionId), $forcePrepend = true)
            );
        }
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addActions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
    }

    /**
     * @param Definition $paymentDefinition
     * @param ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addExtensions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
    }

    /**
     * @param Definition $paymentDefinition
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $contextName
     * @param array $config
     */
    protected function addCommonApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
    }

    /**
     * @param Definition $paymentDefinition
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $contextName
     * @param array $config
     */
    protected function addCommonActions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $paymentDefinition->addMethodCall(
            'addAction',
            array(new Reference('payum.action.capture_details_aggregated_model'))
        );

        $paymentDefinition->addMethodCall(
            'addAction',
            array(new Reference('payum.action.sync_details_aggregated_model'))
        );

        $paymentDefinition->addMethodCall(
            'addAction',
            array(new Reference('payum.action.status_details_aggregated_model'))
        );
    }

    /**
     * @param Definition $paymentDefinition
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $contextName
     * @param array $config
     */
    protected function addCommonExtensions(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $paymentDefinition->addMethodCall(
            'addExtension', 
            array(new Reference('payum.extension.endless_cycle_detector'))
        );

        if (version_compare(Kernel::VERSION, '2.2.0', '>=')) {
            $paymentDefinition->addMethodCall(
                'addExtension',
                array(new Reference('payum.extension.log_executed_actions'))
            );
            $paymentDefinition->addMethodCall(
                'addExtension',
                array(new Reference('payum.extension.logger'))
            );
        }
    }
}