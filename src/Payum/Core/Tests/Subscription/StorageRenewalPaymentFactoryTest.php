<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Subscription;

use Money\Money;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\Subscription;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Subscription\StorageRenewalPaymentFactory;
use PHPUnit\Framework\TestCase;

final class StorageRenewalPaymentFactoryTest extends TestCase
{
    public function testShouldLinkThePaymentToTheSubscriptionItsCustomerAndItsStoredMethod(): void
    {
        $customer = new Customer();
        $customer->setCustomerId('cus_1');
        $method = new StoredPaymentMethod();
        $subscription = new Subscription();
        $subscription->setCustomer($customer);
        $subscription->setStoredPaymentMethod($method);
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('create')->willReturn(new Payment());
        $storages = $this->createMock(StorageRegistryInterface::class);
        $storages->method('getStorage')->with(Payment::class)->willReturn($storage);

        $payment = (new StorageRenewalPaymentFactory($storages))->create($subscription, new Renewal('in_1', Money::EUR(1500), PaymentStatus::Captured));

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($subscription, $payment->getSubscription());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertSame($method, $payment->getStoredPaymentMethod());
    }
}
