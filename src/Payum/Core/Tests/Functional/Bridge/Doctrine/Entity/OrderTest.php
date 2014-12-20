<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\Order;

class OrderTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldAllowPersistEmpty()
    {
        $this->em->persist(new Order());
        $this->em->flush();
    }

    /**
     * @test
     */
    public function shouldAllowPersistWithSomeFieldsSet()
    {
        $order = new Order();
        $order->setTotalAmount(100);
        $order->setCurrencyCode('USD');
        $order->setNumber('aNum');
        $order->setDetails('aDesc');
        $order->setClientEmail('anEmail');
        $order->setClientId('anId');
        $order->setDetails(array('bar1', 'bar2' => 'theBar2'));

        $this->em->persist($order);
        $this->em->flush();
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedOrder()
    {
        $order = new Order();

        $this->em->persist($order);
        $this->em->flush();

        $id = $order->getId();

        $this->em->clear();

        $foundOrder = $this->em->find(get_class($order), $id);

        //guard
        $this->assertNotSame($order, $foundOrder);

        $this->assertEquals($order->getId(), $foundOrder->getId());
    }

    /**
     * @test
     */
    public function shouldNotStoreSensitiveValue()
    {
        $order = new Order();
        $order->setDetails(array('cardNumber' => new SensitiveValue('theCardNumber')));

        $this->em->persist($order);
        $this->em->flush();

        $this->em->refresh($order);

        $this->assertEquals(array('cardNumber' => array()), $order->getDetails());
    }
}
