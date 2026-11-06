<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\Payment;
use Payum\Core\Tests\Mocks\Entity\PaymentWithOwnStatus;

final class PaymentTest extends OrmTest
{
    public function testShouldAllowPersistEmpty(): void
    {
        $entity = new Payment();
        $this->em->persist($entity);
        $this->em->flush();

        $this->assertSame([$entity], $this->em->getRepository(Payment::class)->findAll());
    }

    public function testShouldAllowPersistWithSomeFieldsSet(): void
    {
        $order = new Payment();
        $order->setTotalAmount(100);
        $order->setCurrencyCode('USD');
        $order->setNumber('aNum');
        $order->setDetails('aDesc');
        $order->setClientEmail('anEmail');
        $order->setClientId('anId');
        $order->setDetails([
            'bar1',
            'bar2' => 'theBar2',
        ]);

        $this->em->persist($order);
        $this->em->flush();

        $this->assertSame([$order], $this->em->getRepository(Payment::class)->findAll());
    }

    public function testShouldAllowFindPersistedOrder(): void
    {
        $order = new Payment();

        $this->em->persist($order);
        $this->em->flush();

        $id = $order->getId();

        $this->em->clear();

        $foundOrder = $this->em->find($order::class, $id);

        // guard
        $this->assertNotSame($order, $foundOrder);
        $this->assertInstanceOf(Payment::class, $foundOrder);

        $this->assertSame($order->getId(), $foundOrder->getId());
    }

    public function testShouldNotStoreSensitiveValue(): void
    {
        $order = new Payment();
        $order->setDetails([
            'cardNumber' => new SensitiveValue('theCardNumber'),
        ]);

        $this->em->persist($order);
        $this->em->flush();

        $this->em->refresh($order);

        $this->assertEquals([
            'cardNumber' => null,
        ], $order->getDetails());
    }

    public function testShouldStoreTheStatus(): void
    {
        $payment = new Payment();
        $payment->setStatus(PaymentStatus::Captured);

        $this->em->persist($payment);
        $this->em->flush();
        $this->em->clear();

        $this->assertSame(PaymentStatus::Captured, $this->em->find(Payment::class, $payment->getId())->getStatus());
    }

    public function testShouldReadAStatusStoredBeforeItWasTrackedAsUnknown(): void
    {
        $payment = new Payment();

        $this->em->persist($payment);
        $this->em->flush();
        $this->em->getConnection()->executeStatement('UPDATE orders SET payum_status = NULL');
        $this->em->clear();

        $this->assertSame(PaymentStatus::Unknown, $this->em->find(Payment::class, $payment->getId())->getStatus());
    }

    public function testShouldLetASubclassMapItsOwnStatus(): void
    {
        $payment = new PaymentWithOwnStatus();
        $payment->setStatus(PaymentStatus::Refunded);

        $this->em->persist($payment);
        $this->em->flush();
        $this->em->clear();

        $this->assertSame(PaymentStatus::Refunded, $this->em->find(PaymentWithOwnStatus::class, $payment->getId())->getStatus());
    }
}
