<?php
namespace Payum\Core\Tests\Bridge\Symfony;

use Payum\Core\Bridge\Symfony\ContainerAwareRegistry;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ContainerAwareRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass(ContainerAwareRegistry::class);

        $this->assertTrue($rc->isSubclassOf(AbstractRegistry::class));
    }

    /**
     * @test
     */
    public function shouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass(ContainerAwareRegistry::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithGatewaysStoragesAndTheirDefaultNames()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        new ContainerAwareRegistry($gateways, $storages);
    }

    /**
     * @test
     */
    public function shouldReturnGatewaySetToContainer()
    {
        $gateways = array('fooGateway' => 'fooGatewayServiceId');
        $storages = array();

        $container = new Container;
        $container->set('fooGatewayServiceId', $this->getMock(GatewayInterface::class));

        $registry = new ContainerAwareRegistry($gateways, $storages);
        $registry->setContainer($container);
        
        $this->assertSame(
            $container->get('fooGatewayServiceId'),
            $registry->getGateway('fooGateway')
        );
    }

    /**
     * @test
     */
    public function shouldReturnStorageSetToContainer()
    {
        $gateways = array();
        $storages = array(
            'stdClass' =>  'fooStorageServiceId'
        );

        $container = new Container;
        $container->set('fooStorageServiceId', $this->getMock(StorageInterface::class));

        $registry = new ContainerAwareRegistry($gateways, $storages);
        $registry->setContainer($container);

        $this->assertSame($container->get('fooStorageServiceId'), $registry->getStorage('stdClass'));
    }

    /**
     * @test
     */
    public function shouldReturnGatewayFactorySetToContainer()
    {
        $container = new Container;
        $container->set('fooFactoryServiceId', $this->getMock(StorageInterface::class));

        $registry = new ContainerAwareRegistry(array(), array(), array(
            'fooName' => 'fooFactoryServiceId',
        ));
        $registry->setContainer($container);

        $this->assertSame($container->get('fooFactoryServiceId'), $registry->getGatewayFactory('fooName'));
    }
}
