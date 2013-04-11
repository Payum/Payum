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

class MainConfigurationTest extends  \PHPUnit_Framework_TestCase
{
    protected $paymentFactories = array();

    protected $storageFactories = array();
    
    protected function setUp()
    {
        $this->paymentFactories = array(
            new FooPaymentFactory(),
            new BarPaymentFactory()
        );
        $this->storageFactories = array(
            new FooStorageFactory(),
            new BarStorageFactory()
        );
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
                        'foo_payment' => array( 
                            'foo_opt' => 'foo'
                        ),
                        'storages' => array(
                            'bar' => array(
                                'bar_storage' => array(
                                    'bar_opt' => 'bar'
                                ),
                            ),
                            'foo' => array(
                                'bar_storage' => array(
                                    'bar_opt' => 'bar'
                                ),
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
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context.storages.bar": Only one storage per entry could be selected
     */
    public function throwIfTryToAddMoreThenOneStorageForOneEntry()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                        'storages' => array(
                            'bar' => array(
                                'foo_storage' => array(
                                    'foo_opt' => 'bar'
                                ),
                                'bar_storage' => array(
                                    'bar_opt' => 'bar'
                                )
                            ),
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
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context.storages.bar": At least one storage must be configured.
     */
    public function throwIfStorageEntryDefinedWithoutConcreteStorage()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                        'storages' => array(
                            'bar' => array(),
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
                    'a_context' => array()
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
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $paymentFactories = array(
            new PaypalExpressCheckoutNvpPaymentFactory()
        );
        
        $configuration = new MainConfiguration($paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
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
    public function shouldPassConfigurationProcessingWithDoctrineStorageFactory()
    {
        if (false == class_exists('Doctrine\ORM\Configuration')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $storageFactories = array(
            new DoctrineStorageFactory()
        );

        $configuration = new MainConfiguration($this->paymentFactories, $storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'contexts' => array(
                    'a_context' => array(
                        'storages' => array(
                            'foo' => array(
                                'doctrine' => array(
                                    'driver' => 'aDriver',
                                    'model_class' => 'aClass'
                                ) 
                            ),
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
                        'storages' => array(
                            'foo' => array(
                                'filesystem' => array(
                                    'storage_dir' => 'a_dir',
                                    'model_class' => 'aClass',
                                    'id_property' => 'aProp',
                                ),
                            ),
                        ),
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
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
