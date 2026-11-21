<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaValidator;
use DOMDocument;
use Money\Money;
use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\IntervalUnit;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Tests\Mocks\Entity\Customer;
use Payum\Core\Tests\Mocks\Entity\Payment;
use Payum\Core\Tests\Mocks\Entity\PaymentWithLinks;
use Payum\Core\Tests\Mocks\Entity\StoredPaymentMethod;
use Payum\Core\Tests\Mocks\Entity\Subscription;
use Payum\Core\Tests\Mocks\Entity\User;

final class MappingTest extends OrmTest
{
    public function testMappingsShouldValidate(): void
    {
        $this->assertSame([], (new SchemaValidator($this->em))->validateMapping());
    }

    public function testShouldValidateTheShippedPaymentMappingWithoutLinks(): void
    {
        $metadata = $this->em->getClassMetadata(Payment::class);

        $this->assertFalse($metadata->hasAssociation('customer'));
        $this->assertFalse($metadata->hasAssociation('storedPaymentMethod'));
        $this->assertFalse($metadata->hasAssociation('subscription'));
        $this->assertSame([], (new SchemaValidator($this->em))->validateClass($metadata));
    }

    public function testShouldPersistAPaymentWithItsCustomerAndStoredMethod(): void
    {
        $customer = new Customer();
        $customer->setCustomerId('user-1');

        $method = new StoredPaymentMethod();
        $method->setGatewayName('stripe');
        $method->setCustomer($customer);

        $payment = new PaymentWithLinks();
        $payment->setCustomer($customer);
        $payment->setStoredPaymentMethod($method);

        $this->em->persist($customer);
        $this->em->persist($method);
        $this->em->persist($payment);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->find(PaymentWithLinks::class, $payment->getId());
        $this->assertInstanceOf(PaymentWithLinks::class, $found);

        $foundMethod = $found->getStoredPaymentMethod();
        $this->assertInstanceOf(StoredPaymentMethod::class, $foundMethod);

        $this->assertSame('user-1', $found->getCustomer()?->getCustomerId());
        $this->assertSame('stripe', $foundMethod->getGatewayName());
        $this->assertSame('user-1', $foundMethod->getCustomer()?->getCustomerId());
    }

    public function testShouldPersistAPaymentOfASubscription(): void
    {
        $customer = new Customer();
        $customer->setCustomerId('user-2');

        $subscription = new Subscription();
        $subscription->setGatewayName('stripe');
        $subscription->setSchedule(SubscriptionSchedule::Application);
        $subscription->setCustomer($customer);
        $subscription->setMoney(Money::EUR(1500));
        $subscription->setInterval(new BillingInterval(IntervalUnit::Month, 3));
        $subscription->setStartAt(new DateTimeImmutable('2026-02-01 10:00:00'));
        $subscription->setStatus(SubscriptionStatus::Active);

        $payment = new PaymentWithLinks();
        $payment->setSubscription($subscription);

        $this->em->persist($customer);
        $this->em->persist($subscription);
        $this->em->persist($payment);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->find(PaymentWithLinks::class, $payment->getId());
        $this->assertInstanceOf(PaymentWithLinks::class, $found);

        $foundSubscription = $found->getSubscription();
        $this->assertInstanceOf(Subscription::class, $foundSubscription);
        $this->assertSame(SubscriptionSchedule::Application, $foundSubscription->getSchedule());
        $this->assertSame(SubscriptionStatus::Active, $foundSubscription->getStatus());
        $this->assertSame('1500', $foundSubscription->getMoney()?->getAmount());
        $this->assertSame('3 month', $foundSubscription->getInterval()?->toString());
        $this->assertSame('2026-02-01', $foundSubscription->getStartAt()?->format('Y-m-d'));
        $this->assertSame('user-2', $foundSubscription->getCustomer()?->getCustomerId());
    }

    public function testShouldPersistAPaymentWithoutLinks(): void
    {
        $payment = new PaymentWithLinks();
        $this->em->persist($payment);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->find(PaymentWithLinks::class, $payment->getId());
        $this->assertInstanceOf(PaymentWithLinks::class, $found);

        $this->assertNull($found->getCustomer());
        $this->assertNull($found->getStoredPaymentMethod());
        $this->assertNull($found->getSubscription());
    }

    public function testShouldPersistTheGatewayCustomerIdsOfAUserEntity(): void
    {
        $user = new User();
        $user->setGatewayCustomerId('stripe', 'cus_1');
        $user->setGatewayCustomerId('mollie', 'cst_1');

        $this->em->persist($user);
        $this->em->flush();
        $this->em->clear();

        $found = $this->em->find(User::class, (int) $user->getCustomerId());
        $this->assertInstanceOf(User::class, $found);

        $this->assertSame('cus_1', $found->getGatewayCustomerId('stripe'));
        $this->assertSame('cst_1', $found->getGatewayCustomerId('mollie'));
    }

    public function testOdmMappingsShouldBeWellFormed(): void
    {
        foreach (glob(dirname(__DIR__, 4) . '/Bridge/Doctrine/Resources/mapping/*.mongodb.xml') as $file) {
            $document = new DOMDocument();
            $this->assertTrue($document->load($file), $file);
            $this->assertSame('doctrine-mongo-mapping', $document->documentElement->localName, $file);
        }
    }
}
