<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\Payment;

class PaymentTest extends MongoTest
{
    /**
     * @test
     */
    public function shouldAllowPersistEmpty()
    {
        $this->dm->persist(new Payment());
        $this->dm->flush();
    }

    /**
     * @test
     */
    public function shouldAllowPersistWithSomeFieldsSet()
    {
        $order = new Payment();
        $order->setTotalAmount(100);
        $order->setCurrencyCode('USD');
        $order->setNumber('aNum');
        $order->setDetails('aDesc');
        $order->setClientEmail('anEmail');
        $order->setClientId('anId');
        $order->setDetails(array('bar1', 'bar2' => 'theBar2'));

        $this->dm->persist($order);
        $this->dm->flush();
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedOrder()
    {
        $order = new Payment();

        $this->dm->persist($order);
        $this->dm->flush();

        $id = $order->getId();

        $this->dm->clear();

        $foundOrder = $this->dm->find(get_class($order), $id);

        //guard
        $this->assertNotSame($order, $foundOrder);

        $this->assertEquals($order->getId(), $foundOrder->getId());
    }

    /**
     * @test
     */
    public function shouldNotStoreSensitiveValue()
    {
        $order = new Payment();
        $order->setDetails(array('cardNumber' => new SensitiveValue('theCardNumber')));

        $this->dm->persist($order);
        $this->dm->flush();

        $this->dm->refresh($order);

        $this->assertEquals(array('cardNumber' =>  null), $order->getDetails());
    }
}
