<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;

class MainConfigurationTest extends  \PHPUnit_Framework_TestCase
{
    protected $paymentFactories = array();

    protected $storageFactories = array();
    
    protected function setUp()
    {
        $fooPaymentFactory = new FooPaymentFactory();
        $barPaymentFactory = new BarPaymentFactory();
        $this->paymentFactories[$fooPaymentFactory->getName()] = $fooPaymentFactory;
        $this->paymentFactories[$barPaymentFactory->getName()] = $barPaymentFactory;
        
        $fooStorageFactory = new FooStorageFactory();
        $barStorageFactory = new BarStorageFactory();
        $this->storageFactories[$fooStorageFactory->getName()] = $fooStorageFactory;
        $this->storageFactories[$barStorageFactory->getName()] = $barStorageFactory;
    }
    
    /**
     * @test
     */
    public function couldBeConstructedWithArrayOfPaymentFactoriesAndStorageFactories()
    {
        new MainConfiguration($this->paymentFactories, $this->storageFactories);
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessing()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);
        
        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_payment' => array( 
                            'foo_opt' => 'foo'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context": One payment from the  payments available must be selected
     */
    public function throwIfNonePaymentSelected()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassIfNoneStorageSelected()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context": Only one storage per context could be selected
     */
    public function throwIfMoreThenOneStorageSelected()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'foo_storage' => array(
                            'foo_opt' => 'foo'
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context": Only one payment per context could be selected
     */
    public function throwIfMoreThenOnePaymentSelected()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'foo_storage' => array(
                            'foo_opt' => 'foo'
                        ),
                        'bar_payment' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithPaypalExpressCheckoutNvpPaymentFactory()
    {
        $paymentFactories = array(
            new PaypalExpressCheckoutNvpPaymentFactory()
        );
        
        $configuration = new MainConfiguration($paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'paypal_express_checkout_nvp_payment' => array(
                            'api' => array(
                                'options' => array(
                                    'username' => 'aUsername',
                                    'password' => 'aPassword',
                                    'signature' => 'aSignature',
                                    'sandbox' => true
                                )
                            )
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithOmnipayPaymentFactory()
    {
        $paymentFactories = array(
            new OmnipayPaymentFactory()
        );

        $configuration = new MainConfiguration($paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'omnipay_payment' => array(
                            'type' => 'Stripe',
                            'options' => array(
                                'apiKey' => 'abc123',
                            )
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context.omnipay_payment": Given type invalidType is not supported.
     */
    public function throwIfInvalidOmnipayGatewayTypeProvided()
    {
        $paymentFactories = array(
            new OmnipayPaymentFactory()
        );

        $configuration = new MainConfiguration($paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'omnipay_payment' => array(
                            'type' => 'invalidType',
                            'options' => array(
                                'apiKey' => 'abc123',
                            )
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDoctrineStorageFactory()
    {
        $storageFactories = array(
            new DoctrineStorageFactory()
        );

        $configuration = new MainConfiguration($this->paymentFactories, $storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'doctrine_storage' => array(
                            'driver' => 'aDriver',
                            'model_class' => 'aClass'
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithFilesystemStorageFactory()
    {
        $storageFactories = array(
            new FilesystemStorageFactory()
        );

        $configuration = new MainConfiguration($this->paymentFactories, $storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'filesystem_storage' => array(
                            'storage_dir' => 'a_dir',
                            'model_class' => 'aClass',
                            'id_property' => 'aProp',
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithCustomActionDefined()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo',
                            'actions' => array('action.foo', 'action.baz'),
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithCustomApiDefined()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo',
                            'apis' => array('api.foo', 'api.baz'),
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithCustomExtensionsDefined()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo',
                            'extensions' => array('extension.foo', 'extension.baz'),
                        )
                    )
                )
            )
        ));
    }
}

class FooPaymentFactory implements PaymentFactoryInterface
{
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
    }

    public function getName()
    {
        return 'foo_payment';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('foo_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}

class BarPaymentFactory implements PaymentFactoryInterface
{
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
    }

    public function getName()
    {
        return 'bar_payment';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('bar_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}

class FooStorageFactory implements StorageFactoryInterface
{
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
    }

    public function getName()
    {
        return 'foo_storage';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('foo_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}

class BarStorageFactory implements StorageFactoryInterface
{
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
    }

    public function getName()
    {
        return 'bar_storage';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
               ->scalarNode('bar_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
