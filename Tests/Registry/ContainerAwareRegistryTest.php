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
    public function couldBeConstructedWithPaymentsStoragesAndTheirDefaultNames()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        new ContainerAwareRegistry($payments, $storages);
    }

    /**
     * @test
     */
    public function shouldReturnPaymentSetToContainer()
    {
        $payments = array('fooPayment' => 'fooPaymentServiceId');
        $storages = array();

        $container = new Container;
        $container->set('fooPaymentServiceId', $this->getMock('Payum\Core\PaymentInterface'));

        $registry = new ContainerAwareRegistry($payments, $storages);
        $registry->setContainer($container);
        
        $this->assertSame(
            $container->get('fooPaymentServiceId'),
            $registry->getPayment('fooPayment')
        );
    }

    /**
     * @test
     */
    public function shouldReturnStorageSetToContainer()
    {
        $payments = array();
        $storages = array(
            'stdClass' =>  'fooStorageServiceId'
        );

        $container = new Container;
        $container->set('fooStorageServiceId', $this->getMock('Payum\Core\Storage\StorageInterface'));

        $registry = new ContainerAwareRegistry($payments, $storages);
        $registry->setContainer($container);

        $this->assertSame($container->get('fooStorageServiceId'), $registry->getStorage('stdClass'));
    }

    /**
     * @test
     */
    public function shouldReturnPaymentFactorySetToContainer()
    {
        $container = new Container;
        $container->set('fooFactoryServiceId', $this->getMock('Payum\Core\Storage\StorageInterface'));

        $registry = new ContainerAwareRegistry(array(), array(), array(
            'fooName' => 'fooFactoryServiceId',
        ));
        $registry->setContainer($container);

        $this->assertSame($container->get('fooFactoryServiceId'), $registry->getPaymentFactory('fooName'));
    }
}