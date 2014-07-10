<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class PayumExtension extends Extension implements PrependExtensionInterface
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

        $this->loadStorages($config['storages'], $container);
        $this->loadSecurity($config['security'], $container);
        $this->loadContexts($config['contexts'], $container);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // TODO: The logic is disabled due to bug in TwigBundle. See https://github.com/symfony/symfony/pull/9719

//        foreach ($this->paymentFactories as $factory) {
//            if ($factory instanceof PrependExtensionInterface) {
//                $factory->prepend($container);
//            }
//        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadContexts(array $config, ContainerBuilder $container)
    {
        $paymentsIds = array();
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
            $paymentsIds[$contextName] = $paymentId;

            $container->getDefinition($paymentId)->addTag('payum.payment', array(
                'factory' => $paymentFactoryName,
                'context' => $contextName
            ));
        }

        $registryDefinition = $container->getDefinition('payum');
        $registryDefinition->replaceArgument(0, $paymentsIds);
        $registryDefinition->replaceArgument(2, $defaultName);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadStorages(array $config, ContainerBuilder $container)
    {
        $storagesIds = array();
        foreach ($config as $modelClass => $storageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($storageConfig);
            $storageId = $this->storageFactories[$storageFactoryName]->create(
                $container,
                $modelClass,
                $storageConfig[$storageFactoryName]
            );

            $storagesIds[$modelClass] = $storageId;

            if ($storageConfig['payment']['all']) {
                $container->getDefinition($storageId)->addTag('payum.storage_extension',  array('all' => true));
            } else {
                foreach ($storageConfig['payment']['contexts'] as $contextName) {
                    $container->getDefinition($storageId)->addTag('payum.storage_extension',  array('context' => $contextName));
                }

                foreach ($storageConfig['payment']['factories'] as $factory) {
                    $container->getDefinition($storageId)->addTag('payum.storage_extension',  array('factory' => $factory));
                }
            }
        }

        $registryDefinition = $container->getDefinition('payum');

        $registryDefinition->replaceArgument(1, $storagesIds);
    }

    /**
     * @param array $securityConfig
     * @param ContainerBuilder $container
     */
    protected function loadSecurity(array $securityConfig, ContainerBuilder $container)
    {
        foreach ($securityConfig['token_storage'] as $tokenClass => $tokenStorageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($tokenStorageConfig);

            $storageId = $this->storageFactories[$storageFactoryName]->create(
                $container,
                $tokenClass,
                $tokenStorageConfig[$storageFactoryName]
            );

            $container->setDefinition('payum.security.token_storage', new DefinitionDecorator($storageId));
        }
    }

    /**
     * @param Factory\Storage\StorageFactoryInterface $factory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
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
     * @throws \Payum\Core\Exception\InvalidArgumentException
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
