<?php

/* Copyright Astral Game Servers Ltd 2013-2014
 * Developed By
 * Liam Sorsby
 */

namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Storage;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel1StorageFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;


class Propel1StorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorageFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\Propel1StorageFactory');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory'));
    }
    
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Propel1StorageFactory;
    }
    
    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new Propel1StorageFactory;

        $this->assertEquals('propel1', $factory->getName());
    }
    
    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "storage_dir" at path "foo" must be configured.
     */
    public function shouldRequireStorageDirOption()
    {
        $factory = new Propel1StorageFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }
    
    /**
     * @test
     */
    public function shouldSetIdPropertyToNull()
    {
        $factory = new Propel1StorageFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'storage_dir' => '/the/path/to/store/models',
        )));

        $this->assertArrayHasKey('id_property', $config);
        $this->assertNull($config['id_property']);
    }
}
