<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;

class PayumExtension extends Extension
{
    protected $storageFactories = array();

    protected $paymentFactories = array();
    
    public function load(array $configs, ContainerBuilder $container)
    {
        $mainConfig = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($mainConfig, $configs);

        $container->setParameter('payum.template.engine', $config['template']['engine']);

        // load services
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('payum.xml');
        
        $this->loadContexts($config['contexts'], $container);
    }
    
    protected function loadContexts(array $config, ContainerBuilder $container)
    {
        foreach ($config as $contextName => $context) {
            $storageServiceId = null;
            foreach ($context as $serviceName => $service) {
                if (isset($this->paymentFactories[$serviceName])) {
                    $paymentServiceId = $this->paymentFactories[$serviceName]->create($container, $contextName, $service);
                }
                if (isset($this->paymentFactories[$serviceName])) {
                    /** @var $paymentFactory PaymentFactoryInterface */
                    $paymentFactory = $this->paymentFactories[$serviceName];

                    $paymentServiceId = $paymentFactory->create($container, $contextName, $service);
                    $paymentService = $container->getDefinition($paymentServiceId);

                    $paymentService->addMethodCall(
                        'addAction', 
                        array(new Reference('payum.action.capture_payment_instruction_aggregate'))
                    );

                    $paymentService->addMethodCall(
                        'addAction',
                        array(new Reference('payum.action.sync_payment_instruction_aggregate'))
                    );

                    $paymentService->addMethodCall(
                        'addAction',
                        array(new Reference('payum.action.status_payment_instruction_aggregate'))
                    );

                    if (false == empty($config[$contextName][$paymentFactory->getName()]['actions'])) {
                        foreach ($config[$contextName][$paymentFactory->getName()]['actions'] as $actionId) {
                            $paymentService->addMethodCall(
                                'addAction', 
                                array(new Reference($actionId), $forcePrepend = true)
                            );
                        }
                    }

                    if (false == empty($config[$contextName][$paymentFactory->getName()]['apis'])) {
                        foreach ($config[$contextName][$paymentFactory->getName()]['apis'] as $actionId) {
                            $paymentService->addMethodCall(
                                'addApi',
                                array(new Reference($actionId), $forcePrepend = true)
                            );
                        }
                    }

                    if (false == empty($config[$contextName][$paymentFactory->getName()]['extensions'])) {
                        foreach ($config[$contextName][$paymentFactory->getName()]['extensions'] as $actionId) {
                            $paymentService->addMethodCall(
                                'addExtension',
                                array(new Reference($actionId), $forcePrepend = true)
                            );
                        }
                    }
                }
                if (isset($this->storageFactories[$serviceName])) {
                    $storageServiceId = $this->storageFactories[$serviceName]->create($container, $contextName, $service);
                }
            }
            
            if ($storageServiceId) {
                $storageExtensionDefinition = new DefinitionDecorator('payum.extension.storage.prototype');
                $storageExtensionDefinition->replaceArgument(0, new Reference($storageServiceId));
                $storageExtensionDefinition->setPublic(false);
                $storageExtensionId ='payum.context.'.$contextName.'.extension.storage';
                
                $container->setDefinition($storageExtensionId, $storageExtensionDefinition);

                $paymentDefinition = $container->getDefinition($paymentServiceId);
                $paymentDefinition->addMethodCall('addExtension', array(new Reference($storageExtensionId)));
            }

            $paymentDefinition = $container->getDefinition($paymentServiceId);
            $paymentDefinition->addMethodCall('addExtension', array(new Reference('payum.extension.endless_cycle_detector')));

            $contextDefinition = new Definition();
            $contextDefinition->setClass('Payum\Bundle\PayumBundle\Context\LazyContext');
            $contextDefinition->setPublic(false);
            $contextDefinition->addMethodCall('setContainer', array(
                new Reference('service_container')
            ));
            $contextDefinition->setArguments(array(
                $contextName,
                $paymentServiceId,
                $storageServiceId,
            ));
            $contextId = 'payum.context.'.$contextName;
            $container->setDefinition($contextId, $contextDefinition);
            
            $payumPaymentDefinition = $container->getDefinition('payum');
            $payumPaymentDefinition->addMethodCall('addContext', array(
                new Reference($contextId)
            ));
        }
    }

    /**
     * @param Factory\Storage\StorageFactoryInterface $factory
     */
    public function addStorageFactory(StorageFactoryInterface $factory)
    {
        $this->storageFactories[$factory->getName()] = $factory;
    }

    /**
     * @param Factory\Payment\PaymentFactoryInterface $factory
     */
    public function addPaymentFactory(PaymentFactoryInterface $factory)
    {
        $this->paymentFactories[$factory->getName()] = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new MainConfiguration($this->paymentFactories, $this->storageFactories);
    }
}
