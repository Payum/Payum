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
        
        $fooModelClass = get_class($this->getMock('stdClass'));
        $barModelClass = get_class($this->getMock('stdClass'));

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context.storages": The storage entry must be a valid model class. It is set notExistClass
     */
    public function throwIfTryToUseNotValidClassAsStorageEntry()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
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
                        'storages' => array(
                            'notExistClass' => array(
                                'foo_storage' => array(
                                    'foo_opt' => 'bar'
                                ),
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
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context.storages.stdClass": Only one storage per entry could be selected
     */
    public function throwIfTryToAddMoreThenOneStorageForOneEntry()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
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
                        'storages' => array(
                            'stdClass' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.contexts.a_context.storages.stdClass": At least one storage must be configured.
     */
    public function throwIfStorageEntryDefinedWithoutConcreteStorage()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
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
                        'storages' => array(
                            'stdClass' => array(),
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
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
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
                        'Payum\Model\Token' => array(
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
                        'Payum\Model\Token' => array(
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
                        'Payum\Model\Token' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.security.token_storage": The token class must implement `Payum\Security\TokenInterface` interface
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
    public function create(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config)
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
    public function create(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config)
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