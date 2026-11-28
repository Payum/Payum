<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\DeletePaymentMethodCommand;
use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\DeletePaymentMethodResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\SavePaymentMethodResult;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Offline\OfflineConfig;
use PHPUnit\Framework\TestCase;

final class StoredPaymentMethodTest extends TestCase
{
    /**
     * @var Payum<object>
     */
    private Payum $payum;

    protected function setUp(): void
    {
        $this->payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('offline', new OfflineConfig())
            ->getPayum();
    }

    public function testShouldDeclareStoredPaymentMethodsAndRecurring(): void
    {
        $gateway = $this->payum->getGateway('offline');
        $this->assertInstanceOf(Gateway::class, $gateway);
        $capabilities = $gateway->capabilities();

        $this->assertContains(Capability::StoredPaymentMethods, $capabilities);
        $this->assertContains(Capability::Recurring, $capabilities);
        $this->assertContains(Capability::SavePaymentMethod, $capabilities);
        $this->assertContains(Capability::DeletePaymentMethod, $capabilities);
    }

    public function testCaptureShouldSaveTheMethod(): void
    {
        $method = $this->method();
        $payment = $this->payment($method);

        $result = $this->payum->getGateway('offline')->execute(CaptureCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertStringStartsWith('offline_', (string) $method->getReference());
        $this->assertSame(PaymentMethodType::Other, $method->getType());
    }

    public function testAuthorizeShouldSaveTheMethod(): void
    {
        $method = $this->method();
        $payment = $this->payment($method);

        $result = $this->payum->getGateway('offline')->execute(AuthorizeCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Authorized, $result->status);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertStringStartsWith('offline_', (string) $method->getReference());
    }

    public function testMerchantInitiatedChargeShouldCapture(): void
    {
        $method = $this->method();
        $method->apply(StoredPaymentMethodDetails::active('offline_existing'));
        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);
        $payment = $this->payment($method);

        $result = $this->payum->getGateway('offline')->execute(CaptureCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertNull($result->storedPaymentMethod());
        $this->assertSame('offline_existing', $method->getReference());
    }

    public function testSaveWithoutPaying(): void
    {
        $method = $this->method();

        $result = $this->payum->getGateway('offline')->execute(SavePaymentMethodCommand::forMethod($method));

        $this->assertInstanceOf(SavePaymentMethodResult::class, $result);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertStringStartsWith('offline_', (string) $method->getReference());
    }

    public function testDelete(): void
    {
        $method = $this->method();
        $this->payum->getGateway('offline')->execute(SavePaymentMethodCommand::forMethod($method));

        $result = $this->payum->getGateway('offline')->execute(DeletePaymentMethodCommand::forMethod($method));

        $this->assertInstanceOf(DeletePaymentMethodResult::class, $result);
        $this->assertSame(StoredPaymentMethodStatus::Revoked, $method->getStatus());
    }

    private function method(): StoredPaymentMethod
    {
        $customer = new Customer();
        $customer->setCustomerId(uniqid('customer_'));
        $this->payum->getStorage(Customer::class)->update($customer);

        $method = new StoredPaymentMethod();
        $method->setGatewayName('offline');
        $method->setCustomer($customer);
        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);

        return $method;
    }

    private function payment(StoredPaymentMethod $method): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setCustomer($method->getCustomer());
        $payment->setStoredPaymentMethod($method);
        $this->payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }
}
