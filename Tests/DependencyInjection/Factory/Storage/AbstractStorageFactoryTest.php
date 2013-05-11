<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Storage;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory;

class AbstractStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementStorageFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory');
        
        $this->assertTrue($rc->implementsInterface('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = $this->createAbstractStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     */
    public function shouldEnabledPaymentExtensionByDefault()
    {
        $factory = $this->createAbstractStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array());
        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertTrue($config['payment_extension']['enabled']);
    }

    /**
     * @test
     */
    public function shouldAllowExplicitlyEnabledPaymentExtension()
    {
        $factory = $this->createAbstractStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array(array(
            'payment_extension' => true
        )));
        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertTrue($config['payment_extension']['enabled']);

        $config = $processor->process($tb->buildTree(), array(array(
            'payment_extension' => array(
                'enabled' => true
            )
        )));
        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertTrue($config['payment_extension']['enabled']);
    }

    /**
     * @test
     */
    public function shouldAllowDisablePaymentExtension()
    {
        $factory = $this->createAbstractStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array());

        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertTrue($config['payment_extension']['enabled']);

        $config = $processor->process($tb->buildTree(), array(array(
            'payment_extension' => false
        )));
        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertFalse($config['payment_extension']['enabled']);

        $config = $processor->process($tb->buildTree(), array(array(
            'payment_extension' => array(
                'enabled' => false
            )
        )));
        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertFalse($config['payment_extension']['enabled']);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $expectedStorage = new Definition();
        
        $factory = $this->createAbstractStorageFactory();
        $factory
            ->expects($this->once())
            ->method('createStorage')
            ->will($this->returnCallback(function() use ($expectedStorage) {
                return $expectedStorage;
            }))
        ;

        $container = new ContainerBuilder;
        $container->setDefinition('aPaymentId', new Definition);

        $actualStorageId = $factory->create($container, 'aContextName', 'A\Model\Class', 'aPaymentId', array(
            'payment_extension' => array(
                'enabled' => false
            )
        ));

        $this->assertEquals('payum.context.aContextName.storage.amodelclass', $actualStorageId);
        $this->assertTrue($container->hasDefinition('payum.context.aContextName.storage.amodelclass'));
        $this->assertSame($expectedStorage, $container->getDefinition('payum.context.aContextName.storage.amodelclass'));
    }

    /**
     * @test
     */
    public function shouldNotCreatePaymentExtensionIfEnabledFalse()
    {
        $expectedStorage = new Definition();

        $factory = $this->createAbstractStorageFactory();
        $factory
            ->expects($this->once())
            ->method('createStorage')
            ->will($this->returnCallback(function() use ($expectedStorage) {
                return $expectedStorage;
            }))
        ;

        $container = new ContainerBuilder;
        $container->setDefinition('aPaymentId', new Definition);

        $factory->create($container, 'aContextName', 'A\Model\Class', 'aPaymentId', array(
            'payment_extension' => array(
                'enabled' => false
            )
        ));

        $this->assertFalse($container->hasDefinition('payum.context.aContextName.extension.storage.amodelclass'));
    }

    /**
     * @test
     */
    public function shouldCreatePaymentExtensionIfEnabledTrue()
    {
        $expectedStorage = new Definition();

        $factory = $this->createAbstractStorageFactory();
        $factory
            ->expects($this->once())
            ->method('createStorage')
            ->will($this->returnCallback(function() use ($expectedStorage) {
                return $expectedStorage;
            }))
        ;

        $container = new ContainerBuilder;
        $container->setDefinition('aPaymentId', new Definition);
        $container->getDefinition('aPaymentId')->setClass('Payum\PaymentInterface');

        $factory->create($container, 'aContextName', 'A\Model\Class', 'aPaymentId', array(
            'payment_extension' => array(
                'enabled' => true
            )
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.extension.storage.amodelclass'));
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition('aPaymentId'), 
            'addExtension',
            new Reference('payum.context.aContextName.extension.storage.amodelclass')
        );
    }

    protected function assertDefinitionContainsMethodCall(Definition $serviceDefinition, $expectedMethod, $expectedFirstArgument)
    {
        foreach ($serviceDefinition->getMethodCalls() as $methodCall) {
            if ($expectedMethod == $methodCall[0] && $expectedFirstArgument == $methodCall[1][0]) {
                return;
            }
        }

        $this->fail(sprintf(
            'Failed assert that service (Class: %s) has method %s been called with first argument %s',
            $serviceDefinition->getClass(),
            $expectedMethod,
            $expectedFirstArgument
        ));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractStorageFactory
     */
    protected function createAbstractStorageFactory()
    {
        return $this->getMockForAbstractClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory');
    }
}