<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfExtension()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\HttpKernel\DependencyInjection\Extension'));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumExtension;
    }

    /**
     * @test
     */
    public function shouldAllowAddGatewayFactory()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addGatewayFactory($factory);
        
        $this->assertAttributeContains($factory, 'gatewaysFactories', $extension);
    }
    

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The gateway factory Mock_GatewayFactoryInterface
     */
    public function throwIfTryToAddGatewayFactoryWithEmptyName()
    {
        $factoryWithEmptyName = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface');
        $factoryWithEmptyName
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(''))
        ;
        
        $extension = new PayumExtension;
        $extension->addGatewayFactory($factoryWithEmptyName);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The gateway factory with such name theFoo already registered
     */
    public function throwIfTryToAddGatewayFactoryWithNameAlreadyAdded()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addGatewayFactory($factory);
        $extension->addGatewayFactory($factory);
    }

    /**
     * @test
     */
    public function shouldAllowAddStorageFactory()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);

        $this->assertAttributeContains($factory, 'storagesFactories', $extension);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The storage factory Mock_StorageFactoryInterface_
     */
    public function throwIfTryToAddStorageFactoryWithEmptyName()
    {
        $factoryWithEmptyName = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');
        $factoryWithEmptyName
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(''))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factoryWithEmptyName);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The storage factory with such name theFoo already registered
     */
    public function throwIfTryToAddStorageGatewayFactoryWithNameAlreadyAdded()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');
        $factory
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);
        $extension->addStorageFactory($factory);
    }

    /**
     * @test
     */
    public function shouldNotAddGenericTwigPathsIfTwigBundleNotRegistered()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array());

        $extension = new PayumExtension;

        $extension->prepend($container);

        $this->assertEmpty($container->getExtensionConfig('twig'));
    }

    /**
     * @test
     */
    public function shouldAddGenericTwigPathsIfGatewayFactoryNotImplementPrependFactoryInterface()
    {
        $factoryMock = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface');
        $factoryMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('aFactory'))
        ;

        //guard
        $this->assertNotInstanceOf('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface', $factoryMock);

        $extension = new PayumExtension;
        $extension->addGatewayFactory($factoryMock);

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('TwigBundle' => 'TwigBundle'));

        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');

        $this->assertContains('PayumCore', $twigConfig[0]['paths']);
        $this->assertContains('PayumSymfonyBridge', $twigConfig[0]['paths']);
    }

    /**
     * @test
     */
    public function shouldPassContainerToGatewayFactoryPrependMethodIfImplementsPrependFactoryInterface()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('TwigBundle' => 'TwigBundle'));

        $factoryMock = $this->getMock('Payum\Bundle\PayumBundle\Tests\DependencyInjection\FactoryPlusPrependExtension');
        $factoryMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('aFactory'))
        ;
        $factoryMock
            ->expects($this->any())
            ->method('prepend')
            ->with($this->identicalTo($container))
            ->will($this->returnCallback(function(ContainerBuilder $container) {
                $container->prependExtensionConfig('twig', array('foo' => 'fooVal'));
                $container->prependExtensionConfig('twig', array('bar' => 'barVal'));
            }))
        ;

        //guard
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface', $factoryMock);

        $extension = new PayumExtension;
        $extension->addGatewayFactory($factoryMock);


        $extension->prepend($container);

        $twigConfig = $container->getExtensionConfig('twig');

        $this->assertContains('barVal', $twigConfig[0]['bar']);
        $this->assertContains('fooVal', $twigConfig[1]['foo']);
        $this->assertContains('PayumCore', $twigConfig[2]['paths']);
        $this->assertContains('PayumSymfonyBridge', $twigConfig[2]['paths']);
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleNotRegistered()
    {
        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array());

        $extension = new PayumExtension;

        $extension->prepend($container);

        $this->assertEmpty($container->getExtensionConfig('doctrine'));
    }

    /**
     * @test
     */
    public function shouldNotAddPayumMappingIfDoctrineBundleRegisteredButDbalNotConfigured()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $extension->prepend($container);

        $this->assertEquals(array(), $container->getExtensionConfig('doctrine'));
    }

    /**
     * @test
     */
    public function shouldAddPayumMappingIfDoctrineBundleRegisteredAndDbalConfigured()
    {
        $extension = new PayumExtension;

        $container = new ContainerBuilder;
        $container->setParameter('kernel.bundles', array('DoctrineBundle' => 'DoctrineBundle'));

        $container->prependExtensionConfig('doctrine', array());
        $container->prependExtensionConfig('doctrine', array(
            'dbal' => 'not empty'
        ));

        $extension->prepend($container);

        $rc = new \ReflectionClass('Payum\Core\Gateway');
        $payumRootDir = dirname($rc->getFileName());

        $this->assertEquals(
            array(
                array(
                    'orm' => array('mappings' => array(
                        'payum' => array(
                            'is_bundle' => false,
                            'type' => 'xml',
                            'dir' => $payumRootDir.'/Bridge/Doctrine/Resources/mapping',
                            'prefix' => 'Payum\Core\Model',
                        )
                    )),
                ),
                array('dbal' => 'not empty'),
                array(),
            ),
            $container->getExtensionConfig('doctrine')
        );
    }
}

interface FactoryPlusPrependExtension extends GatewayFactoryInterface, PrependExtensionInterface
{
}