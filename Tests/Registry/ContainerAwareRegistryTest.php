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

        $this->assertTrue($rc->isSubclassOf('Payum\Registry\AbstractRegistry'));
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

        $paymentName = 'fooName';
        $storageName = 'barName';
        
        new ContainerAwareRegistry($payments, $storages, $paymentName, $storageName);
    }

    /**
     * @test
     */
    public function shouldReturnPaymentSetToContainer()
    {
        $payments = array('fooPayment' => 'fooPaymentServiceId');
        $storages = array();

        $paymentName = 'fooName';
        $storageName = 'barName';
        
        $container = new Container;
        $container->set('fooPaymentServiceId', $this->getMock('Payum\PaymentInterface'));

        $registry = new ContainerAwareRegistry($payments, $storages, $paymentName, $storageName);
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
            'barStorage' => array(
                'stdClass' =>  'fooStorageServiceId'
            )
        );

        $paymentName = 'fooName';
        $storageName = 'barName';

        $container = new Container;
        $container->set('fooStorageServiceId', $this->getMock('Payum\Storage\StorageInterface'));

        $registry = new ContainerAwareRegistry($payments, $storages, $paymentName, $storageName);
        $registry->setContainer($container);

        $this->assertSame(
            $container->get('fooStorageServiceId'),
            $registry->getStorageForClass('stdClass', 'barStorage')
        );
    }
}