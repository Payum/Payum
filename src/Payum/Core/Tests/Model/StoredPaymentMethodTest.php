<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use PHPUnit\Framework\TestCase;

final class StoredPaymentMethodTest extends TestCase
{
    public function testShouldImplementStoredPaymentMethodInterface(): void
    {
        $this->assertInstanceOf(StoredPaymentMethodInterface::class, new StoredPaymentMethod());
    }

    public function testANewMethodIsPending(): void
    {
        $method = new StoredPaymentMethod();

        $this->assertSame(StoredPaymentMethodStatus::Pending, $method->getStatus());
        $this->assertNull($method->getReference());
        $this->assertNull($method->getType());
        $this->assertNull($method->getCustomer());
    }

    public function testApplyShouldWriteEveryGivenField(): void
    {
        $method = new StoredPaymentMethod();

        $method->apply(StoredPaymentMethodDetails::active(
            'pm_1',
            PaymentMethodType::Card,
            'visa',
            '4242',
            12,
            2030,
            'Visa ending 4242',
            'ntid_1',
            'cus_1',
        ));

        $this->assertSame('pm_1', $method->getReference());
        $this->assertSame(PaymentMethodType::Card, $method->getType());
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertSame('visa', $method->getBrand());
        $this->assertSame('4242', $method->getLast4());
        $this->assertSame(12, $method->getExpiryMonth());
        $this->assertSame(2030, $method->getExpiryYear());
        $this->assertSame('Visa ending 4242', $method->getLabel());
        $this->assertSame('ntid_1', $method->getNetworkTransactionId());
    }

    public function testApplyShouldLeaveNullFieldsAlone(): void
    {
        $method = new StoredPaymentMethod();
        $method->apply(StoredPaymentMethodDetails::active('pm_1', PaymentMethodType::Card, 'visa', '4242', 12, 2030, 'Visa', 'ntid_1'));

        $method->apply(StoredPaymentMethodDetails::revoked());

        $this->assertSame(StoredPaymentMethodStatus::Revoked, $method->getStatus());
        $this->assertSame('pm_1', $method->getReference());
        $this->assertSame(PaymentMethodType::Card, $method->getType());
        $this->assertSame('visa', $method->getBrand());
        $this->assertSame('4242', $method->getLast4());
        $this->assertSame(12, $method->getExpiryMonth());
        $this->assertSame(2030, $method->getExpiryYear());
        $this->assertSame('Visa', $method->getLabel());
        $this->assertSame('ntid_1', $method->getNetworkTransactionId());
    }

    public function testShouldBelongToAGatewayAndACustomer(): void
    {
        $customer = new Customer();
        $method = new StoredPaymentMethod();
        $method->setGatewayName('stripe');
        $method->setCustomer($customer);

        $this->assertSame('stripe', $method->getGatewayName());
        $this->assertSame($customer, $method->getCustomer());
    }

    public function testPaymentShouldCarryCustomerAndMethod(): void
    {
        $payment = new Payment();

        $this->assertNull($payment->getCustomer());
        $this->assertNull($payment->getStoredPaymentMethod());

        $customer = new Customer();
        $method = new StoredPaymentMethod();
        $payment->setCustomer($customer);
        $payment->setStoredPaymentMethod($method);

        $this->assertSame($customer, $payment->getCustomer());
        $this->assertSame($method, $payment->getStoredPaymentMethod());
    }
}
