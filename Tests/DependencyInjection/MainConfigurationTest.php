<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class MainConfigurationTest extends  \PHPUnit_Framework_TestCase
{
    protected $gatewayFactories = array();

    protected $storageFactories = array();
    
    protected function setUp()
    {
        $this->gatewayFactories = array(
            new FooGatewayFactory(),
            new BarGatewayFactory()
        );
        $this->storageFactories = array(
            new FooStorageFactory(),
            new BarStorageFactory()
        );
    }
    
    /**
     * @test
     */
    public function couldBeConstructedWithArrayOfGatewayFactoriesAndStorageFactories()
    {
        new MainConfiguration($this->gatewayFactories, $this->storageFactories);
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessing()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);
        
        $processor = new Processor();
        
        $fooModelClass = get_class($this->getMock('stdClass'));
        $barModelClass = get_class($this->getMock('stdClass'));

        $processor->processConfiguration($configuration, array(
            array(
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
    public function shouldAddStoragesToAllGatewayByDefault()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
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

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['all']));
        $this->assertTrue($config['storages'][$fooModelClass]['extension']['all']);

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['factories']));
        $this->assertEquals(array(), $config['storages'][$fooModelClass]['extension']['factories']);

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['gateways']));
        $this->assertEquals(array(), $config['storages'][$fooModelClass]['extension']['gateways']);
    }

    /**
     * @test
     */
    public function shouldAllowDisableAddStoragesToAllGatewayFeature()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'extension' => array(
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

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['all']));
        $this->assertFalse($config['storages'][$fooModelClass]['extension']['all']);
    }

    /**
     * @test
     */
    public function shouldAllowSetConcreteGatewaysWhereToAddStorages()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'extension' => array(
                            'gateways' => array(
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

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['gateways']));
        $this->assertEquals(array('foo', 'bar'), $config['storages'][$fooModelClass]['extension']['gateways']);
    }

    /**
     * @test
     */
    public function shouldAllowSetGatewaysCreatedWithFactoriesWhereToAddStorages()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $fooModelClass = get_class($this->getMock('stdClass'));

        $config = $processor->processConfiguration($configuration, array(
            array(
                'storages' => array(
                    $fooModelClass => array(
                        'extension' => array(
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

        $this->assertTrue(isset($config['storages'][$fooModelClass]['extension']['factories']));
        $this->assertEquals(array('foo', 'bar'), $config['storages'][$fooModelClass]['extension']['factories']);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "payum.storages": The storage entry must be a valid model class. It is set notExistClass
     */
    public function throwIfTryToUseNotValidClassAsStorageEntry()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.gateways.a_gateway": One gateway from the  gateways available must be selected
     */
    public function throwIfNoneGatewaySelected()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array()
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassIfNoneStorageSelected()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.gateways.a_gateway": Only one gateway per gateway could be selected
     */
    public function throwIfMoreThenOneGatewaySelected()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'bar_gateway' => array(
                            'bar_opt' => 'bar'
                        ),
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'foo' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.dynamic_gateways.config_storage": Only one config storage could be configured.
     */
    public function throwIfMoreThenOneGatewayConfigStorageConfigured()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
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
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.dynamic_gateways.config_storage": The config class must implement `Payum\Core\Model\GatewayConfigInterface` interface
     */
    public function throwIfGatewayConfigStorageConfiguredWithModelNotImplementingGatewayConfigInterface()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'stdClass' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
     * @expectedExceptionMessage Invalid configuration for path "payum.dynamic_gateways.config_storage": The storage entry must be a valid model class.
     */
    public function throwIfGatewayConfigStorageConfiguredWithNotModelClass()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'foo' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
     * @expectedExceptionMessage The child node "config_storage" at path "payum.dynamic_gateways" must be configured.
     */
    public function throwIfGatewayConfigStorageNotConfigured()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            array(
                'dynamic_gateways' => array(
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
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
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
    public function shouldOverwriteGatewaysWithSameNameDefinedInDifferentConfigFiles()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'foo_gateway' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            ),
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'bar_gateway' => array(
                            'bar_opt' => 'bar'
                        ),
                    )
                )
            )
        ));

        $this->assertCount(1, $config['gateways']);
        $this->assertCount(1, $config['gateways']['a_gateway']);
        $this->assertArrayHasKey('bar_gateway', $config['gateways']['a_gateway']);
    }

    /**
     * @test
     */
    public function shouldMergeGatewaysWithDifferentNameDefinedInDifferentConfigFiles()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, array(
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_foo_gateway' => array(
                        'foo_gateway' => array(
                            'foo_opt' => 'foo'
                        ),
                    )
                )
            ),
            array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'foo_storage' => array(
                                'foo_opt' => 'foo'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_bar_gateway' => array(
                        'bar_gateway' => array(
                            'bar_opt' => 'bar'
                        ),
                    )
                )
            )
        ));

        $this->assertCount(2, $config['gateways']);
        $this->assertArrayHasKey('a_foo_gateway', $config['gateways']);
        $this->assertArrayHasKey('a_bar_gateway', $config['gateways']);
    }
}

class FooGatewayFactory implements GatewayFactoryInterface
{
    public function create(ContainerBuilder $container, $gatewayName, array $config)
    {
    }

    public function getName()
    {
        return 'foo_gateway';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('foo_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container)
    {
    }
}

class BarGatewayFactory implements GatewayFactoryInterface
{
    public function create(ContainerBuilder $container, $gatewayName, array $config)
    {
    }

    public function getName()
    {
        return 'bar_gateway';
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('bar_opt')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container)
    {
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