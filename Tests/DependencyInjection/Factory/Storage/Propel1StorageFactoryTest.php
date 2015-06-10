<?php

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
    
}
