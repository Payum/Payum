<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

use Payum\Exception\InvalidArgumentException;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Symfony\Component\HttpKernel\Kernel;

class PayumExtension extends Extension
{
    /**
     * @var StorageFactoryInterface[]
     */
    protected $storageFactories = array();

    /**
     * @var PaymentFactoryInterface[]
     */
    protected $paymentFactories = array();

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $mainConfig = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($mainConfig, $configs);

        // load services
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('payum.xml');

        if (version_compare(Kernel::VERSION, '2.2.0', '<')) {
            $container->removeDefinition('payum.extension.log_executed_actions');
            $container->removeDefinition('payum.extension.logger');
        }

        $this->loadSecurity($config['security'], $container);
        $this->loadContexts($config['contexts'], $container);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadContexts(array $config, ContainerBuilder $container)
    {
        $paymentsServicesIds = array();
        $storagesServicesIds = array();
        
        $defaultName = null;
        
        foreach ($config as $contextName => $contextConfig) {
            //use first defined context as default.
            if (false == $defaultName) {
                $defaultName = $contextName;
            }

            $paymentFactoryName = $this->findSelectedPaymentFactoryNameInContextConfig($contextConfig);
            $paymentId = $this->paymentFactories[$paymentFactoryName]->create(
                $container,
                $contextName,
                $contextConfig[$paymentFactoryName]
            );
            $paymentsServicesIds[$contextName] = $paymentId;
            
            foreach ($contextConfig['storages'] as $modelClass => $storageConfig) {
                $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($storageConfig);
                $storageId = $this->storageFactories[$storageFactoryName]->create(
                    $container,
                    $contextName,
                    $modelClass,
                    $paymentId,
                    $storageConfig[$storageFactoryName]
                );
                $storagesServicesIds[$contextName][$modelClass] = $storageId;
            }
        }
        
        $registryDefinition = $container->getDefinition('payum');
        $registryDefinition->replaceArgument(0, $paymentsServicesIds);
        $registryDefinition->replaceArgument(1, $storagesServicesIds);
        $registryDefinition->replaceArgument(2, $defaultName);
        $registryDefinition->replaceArgument(3, $defaultName);
    }

    /**
     * @param array $securityConfig
     * @param ContainerBuilder $container
     */
    protected function loadSecurity(array $securityConfig, ContainerBuilder $container)
    {
        foreach ($securityConfig['token_storage'] as $tokenClass => $tokenStorageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($tokenStorageConfig);

            //force not to use payment extension because we are out of payment scope.
            $tokenStorageConfig[$storageFactoryName]['payment_extension']['enabled'] = false;

            $storageId = $this->storageFactories[$storageFactoryName]->create(
                $container,
                '_security_token',
                $tokenClass,
                null,
                $tokenStorageConfig[$storageFactoryName]
            );

            $container->setDefinition('payum.security.token_storage', new DefinitionDecorator($storageId));
        }
    }

    /**
     * @param Factory\Storage\StorageFactoryInterface $factory
     *
     * @throws \Payum\Exception\InvalidArgumentException
     */
    public function addStorageFactory(StorageFactoryInterface $factory)
    {
        $factoryName = $factory->getName();
        if (empty($factoryName)) {
            throw new InvalidArgumentException(sprintf('The storage factory %s has empty name', get_class($factory)));
        }
        if (array_key_exists($factoryName, $this->storageFactories)) {
            throw new InvalidArgumentException(sprintf('The storage factory with such name %s already registered', $factoryName));
        }
        
        $this->storageFactories[$factoryName] = $factory;
    }

    /**
     * @param Factory\Payment\PaymentFactoryInterface $factory
     *
     * @throws \Payum\Exception\InvalidArgumentException
     */
    public function addPaymentFactory(PaymentFactoryInterface $factory)
    {
        $factoryName = $factory->getName();
        if (empty($factoryName)) {
            throw new InvalidArgumentException(sprintf('The payment factory %s has empty name', get_class($factory)));
        }
        if (isset($this->paymentFactories[$factoryName])) {
            throw new InvalidArgumentException(sprintf('The payment factory with such name %s already registered', $factoryName));
        }
        
        $this->paymentFactories[$factory->getName()] = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new MainConfiguration($this->paymentFactories, $this->storageFactories);
    }

    /**
     * @param array $contextConfig
     *
     * @return string
     */
    protected function findSelectedPaymentFactoryNameInContextConfig($contextConfig)
    {
        foreach ($contextConfig as $name => $value) {
            if (isset($this->paymentFactories[$name])) {
                return $name;
            }
        }
    }

    /**
     * @param array $storageConfig
     *
     * @return string
     */
    protected function findSelectedStorageFactoryNameInStorageConfig($storageConfig)
    {
        foreach ($storageConfig as $name => $value) {
            if (isset($this->storageFactories[$name])) {
                return $name;
            }
        }
    }
}
