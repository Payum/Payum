<?php
namespace Payum\Bundle\PayumBundle\Tests\Registry;

use Symfony\Component\DependencyInjection\Container;

use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry;

class ContainerAwareRegistryTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\AbstractRegistry'));
    }

    /**
     * @test
     */
    public function shouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\ContainerAwareInterface'));
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
        $container->set('fooGatewayServiceId', $this->getMock('Payum\Core\GatewayInterface'));

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
        $container->set('fooStorageServiceId', $this->getMock('Payum\Core\Storage\StorageInterface'));

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
        $container->set('fooFactoryServiceId', $this->getMock('Payum\Core\Storage\StorageInterface'));

        $registry = new ContainerAwareRegistry(array(), array(), array(
            'fooName' => 'fooFactoryServiceId',
        ));
        $registry->setContainer($container);

        $this->assertSame($container->get('fooFactoryServiceId'), $registry->getGatewayFactory('fooName'));
    }
}