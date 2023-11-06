<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\Payment;

class PaymentTest extends OrmTest
{
    public function testShouldAllowPersistEmpty()
    {
        $entity = new Payment();
        $this->em->persist($entity);
        $this->em->flush();

        $this->assertSame([$entity], $this->em->getRepository(Payment::class)->findAll());
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

        $this->em->persist($order);
        $this->em->flush();

        $this->assertSame([$order], $this->em->getRepository(Payment::class)->findAll());
    }

    public function testShouldAllowFindPersistedOrder()
    {
        $order = new Payment();

        $this->em->persist($order);
        $this->em->flush();

        $id = $order->getId();

        $this->em->clear();

        $foundOrder = $this->em->find(get_class($order), $id);

        //guard
        $this->assertNotSame($order, $foundOrder);

        $this->assertSame($order->getId(), $foundOrder->getId());
    }

    public function testShouldNotStoreSensitiveValue()
    {
        $order = new Payment();
        $order->setDetails(array('cardNumber' => new SensitiveValue('theCardNumber')));

        $this->em->persist($order);
        $this->em->flush();

        $this->em->refresh($order);

        $this->assertEquals(array('cardNumber' => null), $order->getDetails());
    }
}
