<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
        
        $fooModelClass = get_class($this->getMock('stdClass'));
        $barModelClass = get_class($this->getMock('stdClass'));

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    $fooModelClass => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                    $barModelClass => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    )
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array( 
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldAddStoragesToAllPaymentByDefault()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    $fooModelClass => array(
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['payment']['all']));
        $this->assertTrue($config['storages'][$fooModelClass]['payment']['all']);

        $this->assertTrue(isset($config['storages'][$fooModelClass]['payment']['factories']));
        $this->assertEquals(array(), $config['storages'][$fooModelClass]['payment']['factories']);

        $this->assertTrue(isset($config['storages'][$fooModelClass]['payment']['contexts']));
        $this->assertEquals(array(), $config['storages'][$fooModelClass]['payment']['contexts']);
    }

    /**
     * @test
     */
    public function shouldAllowDisableAddStoragesToAllPaymentFeature()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    $fooModelClass => array(
                        'payment' => array(
                            'all' => false,
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['payment']['all']));
        $this->assertFalse($config['storages'][$fooModelClass]['payment']['all']);
    }

    /**
     * @test
     */
    public function shouldAllowSetConcretePaymentsWhereToAddStorages()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    $fooModelClass => array(
                        'payment' => array(
                            'contexts' => array(
                                'foo', 'bar'
                            )
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['payment']['contexts']));
        $this->assertEquals(array('foo', 'bar'), $config['storages'][$fooModelClass]['payment']['contexts']);
    }

    /**
     * @test
     */
    public function shouldAllowSetPaymentsCreatedWithFactoriesWhereToAddStorages()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    $fooModelClass => array(
                        'payment' => array(
                            'factories' => array(
                                'foo', 'bar'
                            )
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
            )
        ));

        $this->assertTrue(isset($config['storages'][$fooModelClass]['payment']['factories']));
        $this->assertEquals(array('foo', 'bar'), $config['storages'][$fooModelClass]['payment']['factories']);
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.storages": The storage entry must be a valid model class. It is set notExistClass
     */
    public function throwIfTryToUseNotValidClassAsStorageEntry()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'notExistClass' => array(
                        'foo_storage' => array(
                            'foo_opt' => 'bar'
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            )
        ));
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.storages.stdClass": Only one storage per entry could be selected
     */
    public function throwIfTryToAddMoreThenOneStorageForOneEntry()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'stdClass' => array(
                        'foo_storage' => array(
                            'foo_opt' => 'bar'
                        ),
                        'bar_storage' => array(
                            'bar_opt' => 'bar'
                        )
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.storages.stdClass": At least one storage must be configured.
     */
    public function throwIfStorageEntryDefinedWithoutConcreteStorage()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'stdClass' => array(),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
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
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
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
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
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
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
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
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.security.token_storage": Only one token storage could be configured.
     */
    public function throwIfMoreThenOneTokenStorageConfigured()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        ),
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.security.token_storage": The token class must implement `Payum\Core\Security\TokenInterface` interface
     */
    public function throwIfTokenStorageConfiguredWithModelNotImplementingTokenInterface()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.security.token_storage": The storage entry must be a valid model class.
     */
    public function throwIfTokenStorageConfiguredWithNotModelClass()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'foo' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "security" at path "payum" must be configured.
     */
    public function throwIfSecurityNotConfigured()
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
                    )
                )
            )
        ));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "token_storage" at path "payum.security" must be configured.
     */
    public function throwIfTokenStorageNotConfigured()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                ),
                'contexts' => array(
                    'a_context' => array(
                        'foo_payment' => array(
                            'foo_opt' => 'foo'
                        ),
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
    public function create(ContainerBuilder $container, $modelClass, array $config)
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
    public function create(ContainerBuilder $container, $modelClass, array $config)
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