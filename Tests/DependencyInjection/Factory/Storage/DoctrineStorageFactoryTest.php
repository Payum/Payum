<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Storage;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class DoctrineStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DoctrineStorageFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new DoctrineStorageFactory;

        $this->assertEquals('doctrine', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new DoctrineStorageFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'driver' => 'orm',
            'payment_extension' => array(
                'enabled' => false
            )
        )));
        
        $this->assertArrayHasKey('payment_extension', $config);
        $this->assertArrayHasKey('enabled', $config['payment_extension']);
        $this->assertFalse($config['payment_extension']['enabled']);

        $this->assertArrayHasKey('driver', $config);
        $this->assertEquals('orm', $config['driver']);
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "driver" at path "foo" must be configured.
     */
    public function shouldRequireDriverOption()
    {
        $factory = new DoctrineStorageFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }
}