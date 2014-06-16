<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Storage;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
    public function shouldAllowCreateStorageAndReturnItsId()
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

        $actualStorageId = $factory->create($container, 'A\Model\Class', array());

        $this->assertEquals('payum.storage.a_model_class', $actualStorageId);
        $this->assertTrue($container->hasDefinition($actualStorageId));
        $this->assertSame($expectedStorage, $container->getDefinition($actualStorageId));
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