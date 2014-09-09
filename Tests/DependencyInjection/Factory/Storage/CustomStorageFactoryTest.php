<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Storage;


use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class CustomStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\CustomStorageFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CustomStorageFactory();
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new CustomStorageFactory();

        $this->assertEquals('custom', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'service' => 'service.name',
        )));

        $this->assertArrayHasKey('service', $config);
        $this->assertEquals('service.name', $config['service']);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "service" at path "foo" must be configured.
     */
    public function shouldRequireServiceOption()
    {
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "foo.service" cannot contain an empty value, but got "".
     */
    public function shouldNotAllowEmptyServiceOption()
    {
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'service' => '',
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The path "foo.service" cannot contain an empty value, but got null.
     */
    public function shouldNotAllowNullServiceOption()
    {
        $factory = new CustomStorageFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'service' => null,
        )));
    }

    /**
     * @test
     */
    public function shouldAllowAddShortConfiguration()
    {
        $factory = new CustomStorageFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array('storageId'));

        $this->assertArrayHasKey('service', $config);
        $this->assertEquals('storageId', $config['service']);
    }

    /**
     * @test
     */
    public function shouldCreateServiceDefinition()
    {
        $serviceName = 'service.name';
        $that = $this;

        $containerBuilder = $this->createContainerBuilderMock();
        $containerBuilder
            ->expects($this->once())
            ->method('setDefinition')
            ->with($this->anything(), $this->callback(function($definition) use ($serviceName, $that) {
                $that->assertInstanceOf('Symfony\Component\DependencyInjection\Definition', $definition);
                $that->assertEquals($serviceName, $definition->getParent());

                return true;
            }))
        ;

        $factory = new CustomStorageFactory();
        $factory->create($containerBuilder, 'class', array('service' => $serviceName));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function createContainerBuilderMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array(), array(), '', false);
    }
}
