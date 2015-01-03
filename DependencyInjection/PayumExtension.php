<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Core\Bridge\Twig\TwigFactory;
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
        $loader->load('security.xml');
        $loader->load('form.xml');

        if ($container->getParameter('kernel.debug')) {
            $loader->load('debug.xml');
        }

        $this->loadStorages($config['storages'], $container);
        $this->loadSecurity($config['security'], $container);
        $this->loadPayments($config['payments'], $container);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
            'paths' => array(
                TwigFactory::guessViewsPath('Payum\Core\Payment') => 'PayumCore',
                TwigFactory::guessViewsPath('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter') => 'PayumSymfonyBridge',
            )
        ));

        foreach ($this->paymentFactories as $factory) {
            if ($factory instanceof PrependExtensionInterface) {
                $factory->prepend($container);
            }
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadPayments(array $config, ContainerBuilder $container)
    {
        $paymentsIds = array();
        $defaultName = null;
        foreach ($config as $paymentName => $paymentConfig) {
            //use first defined payment as default.
            if (false == $defaultName) {
                $defaultName = $paymentName;
            }

            $paymentFactoryName = $this->findSelectedPaymentFactoryNameInPaymentConfig($paymentConfig);
            $paymentId = $this->paymentFactories[$paymentFactoryName]->create(
                $container,
                $paymentName,
                $paymentConfig[$paymentFactoryName]
            );
            $paymentsIds[$paymentName] = $paymentId;

            $container->getDefinition($paymentId)->addTag('payum.payment', array(
                'factory' => $paymentFactoryName,
                'payment' => $paymentName
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

            if ($storageConfig['extension']['all']) {
                $container->getDefinition($storageId)->addTag('payum.storage_extension',  array('all' => true));
            } else {
                foreach ($storageConfig['extension']['payments'] as $paymentName) {
                    $container->getDefinition($storageId)->addTag('payum.storage_extension',  array('payment' => $paymentName));
                }

                foreach ($storageConfig['extension']['factories'] as $factory) {
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
     * @param array $paymentConfig
     *
     * @return string
     */
    protected function findSelectedPaymentFactoryNameInPaymentConfig($paymentConfig)
    {
        foreach ($paymentConfig as $name => $value) {
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
