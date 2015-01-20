<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
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

        if (isset($config['dynamic_payments'])) {
            $this->loadDynamicPayments($config['dynamic_payments'], $container);
        };
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['TwigBundle'])) {
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

        if (isset($bundles['DoctrineBundle'])) {
            $rc = new \ReflectionClass('Payum\Core\Payment');
            $payumRootDir = dirname($rc->getFileName());

            $container->prependExtensionConfig('doctrine', array(
                'orm' => array(
                    'mappings' => array(
                        'payum' => array(
                            'is_bundle' => false,
                            'type' => 'xml',
                            'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                            'prefix' => 'Payum\Core\Model',
                        ),
                    ),
                ),
            ));
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadPayments(array $config, ContainerBuilder $container)
    {
        foreach ($this->paymentFactories as $factory) {
            $factory->load($container);
        }

        foreach ($config as $paymentName => $paymentConfig) {
            $paymentFactoryName = $this->findSelectedPaymentFactoryNameInPaymentConfig($paymentConfig);
            $paymentId = $this->paymentFactories[$paymentFactoryName]->create(
                $container,
                $paymentName,
                $paymentConfig[$paymentFactoryName]
            );

            $container->getDefinition($paymentId)->addTag('payum.payment', array(
                'factory' => $paymentFactoryName,
                'payment' => $paymentName
            ));
        }
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function loadStorages(array $config, ContainerBuilder $container)
    {
        foreach ($config as $modelClass => $storageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($storageConfig);
            $storageId = $this->storageFactories[$storageFactoryName]->create(
                $container,
                $modelClass,
                $storageConfig[$storageFactoryName]
            );

            $container->getDefinition($storageId)->addTag('payum.storage', array('model_class' => $modelClass));

            if (false !== strpos($storageId, '.storage.')) {
                $storageExtensionId = str_replace('.storage.', '.extension.storage.', $storageId);
            } else {
                throw new LogicException(sprintf('In order to add storage to extension the storage %id has to contains ".storage." inside.', $storageId));
            }

            $storageExtension = new DefinitionDecorator('payum.extension.storage.prototype');
            $storageExtension->replaceArgument(0, new Reference($storageId));
            $storageExtension->setPublic(true);
            $container->setDefinition($storageExtensionId, $storageExtension);

            if ($storageConfig['extension']['all']) {
                $storageExtension->addTag('payum.extension', array('all' => true));
            } else {
                foreach ($storageConfig['extension']['payments'] as $paymentName) {
                    $storageExtension->addTag('payum.extension', array('payment' => $paymentName));
                }

                foreach ($storageConfig['extension']['factories'] as $factory) {
                    $storageExtension->addTag('payum.extension', array('factory' => $factory));
                }
            }
        }
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
     * @param array $dynamicPaymentsConfig
     * @param ContainerBuilder $container
     */
    protected function loadDynamicPayments(array $dynamicPaymentsConfig, ContainerBuilder $container)
    {
        $configClass = null;
        $configStorage = null;
        foreach ($dynamicPaymentsConfig['config_storage'] as $configClass => $configStorageConfig) {
            $storageFactoryName = $this->findSelectedStorageFactoryNameInStorageConfig($configStorageConfig);

            $configStorage = $this->storageFactories[$storageFactoryName]->create(
                $container,
                $configClass,
                $configStorageConfig[$storageFactoryName]
            );

            $container->setDefinition('payum.dynamic_payments.config_storage', new DefinitionDecorator($configStorage));
        }

        $registry =  new Definition('Payum\Core\Registry\DynamicRegistry', array(
            new Reference('payum.dynamic_payments.config_storage'),
            new Reference('payum.static_registry')
        ));
        $container->setDefinition('payum.dynamic_registry', $registry);
        $container->setAlias('payum', new Alias('payum.dynamic_registry'));

        if (isset($dynamicPaymentsConfig['sonata_admin'])) {
            $paymentConfigAdmin =  new Definition('Payum\Bundle\PayumBundle\Sonata\PaymentConfigAdmin', array(
                null,
                $configClass,
                null
            ));
            $paymentConfigAdmin->addMethodCall('setFormFactory', array(new Reference('form.factory')));
            $paymentConfigAdmin->addTag('sonata.admin', array(
                'manager_type' => 'orm',
                'group' => "Payments",
                'label' =>  "Configs",
            ));

            $container->setDefinition('payum.dynamic_payments.payment_config_admin', $paymentConfigAdmin);
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
