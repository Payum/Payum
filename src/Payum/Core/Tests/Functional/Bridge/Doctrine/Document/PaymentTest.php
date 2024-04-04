<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\Payment;

class PaymentTest extends MongoTest
{
    public function testShouldAllowPersistEmpty()
    {
        $document = new Payment();
        $this->dm->persist($document);
        $this->dm->flush();

        $this->assertSame([$document], $this->dm->getRepository(Payment::class)->findAll());
    }

    public function testShouldAllowPersistWithSomeFieldsSet()
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

        $this->assertSame([$order], $this->dm->getRepository(Payment::class)->findAll());
    }

    public function testShouldAllowFindPersistedOrder()
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

    public function testShouldNotStoreSensitiveValue()
    {
        $order = new Payment();
        $order->setDetails(array('cardNumber' => new SensitiveValue('theCardNumber')));

        $this->dm->persist($order);
        $this->dm->flush();

        $this->dm->refresh($order);

        if (PHP_VERSION_ID >= 70400) {
            $this->assertEquals(array('cardNumber' => new SensitiveValue(null)), $order->getDetails());
            $this->assertNotEquals(array('cardNumber' => new SensitiveValue('theCardNumber')), $order->getDetails());
        } else {
            $this->assertEquals(array('cardNumber' => null), $order->getDetails());
        }
    }
}
