<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\ChargeReason;
use Payum\Core\Command\DeletePaymentMethodCommand;
use Payum\Core\Command\Initiator;
use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Event\PaymentMethodStatusChanged;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\Customer;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Core\Security\TokenPurpose;
use Payum\Offline\OfflineConfig;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Runs the samples of docs/stored-payment-methods.md against the Offline gateway.
 */
final class StoredPaymentMethodDocsTest extends TestCase
{
    /**
     * @var Payum<object>
     */
    private Payum $payum;

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'shop.example.com';
        $_SERVER['SCRIPT_NAME'] = '/prepare.php';

        $this->dispatcher = new EventDispatcher();
        $this->payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(EventDispatcherInterface::class, $this->dispatcher)
            ->registerGateway('offline', new OfflineConfig())
            ->getPayum();

        $customer = new Customer();
        $customer->setCustomerId('customer-42');
        $customer->setEmail('customer@example.com');

        $this->payum->getStorage(Customer::class)->update($customer);
    }

    public function testShouldReadTheCapabilities(): void
    {
        $gateway = $this->payum->getGateway('offline');
        $this->assertInstanceOf(Gateway::class, $gateway);
        $capabilities = $gateway->capabilities();

        $this->assertContains(Capability::StoredPaymentMethods, $capabilities);
        $this->assertContains(Capability::Recurring, $capabilities);
        $this->assertContains(Capability::SavePaymentMethod, $capabilities);
        $this->assertContains(Capability::DeletePaymentMethod, $capabilities);
    }

    public function testShouldSaveTheMethodDuringTheFirstPayment(): void
    {
        $payum = $this->payum;

        $customer = $payum->getStorage(Customer::class)->find('customer-42');
        $this->assertInstanceOf(Customer::class, $customer);

        $method = new StoredPaymentMethod();
        $method->setGatewayName('offline');
        $method->setCustomer($customer);
        $payum->getStorage(StoredPaymentMethod::class)->update($method);

        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setCustomer($customer);
        $payment->setStoredPaymentMethod($method);

        $captureToken = $payum->prepare('offline', $payment);

        $response = $payum->capture(Request::create($captureToken->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $payment = $payum->done(Request::create($response->getTargetUrl()));
        $this->assertInstanceOf(Payment::class, $payment);
        $method = $payment->getStoredPaymentMethod();

        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
        $this->assertSame(StoredPaymentMethodStatus::Active, $method?->getStatus());

        $identity = $payum->getStorage(StoredPaymentMethod::class)->identify($method);
        $stored = $payum->getStorage(StoredPaymentMethod::class)->find($identity->getId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $stored);
        $this->assertSame(StoredPaymentMethodStatus::Active, $stored->getStatus());
        $this->assertSame($method->getReference(), $stored->getReference());
    }

    public function testShouldChargeASavedMethod(): void
    {
        $payum = $this->payum;
        $methodId = $this->savedMethodId();

        $method = $payum->getStorage(StoredPaymentMethod::class)->find($methodId);
        $this->assertInstanceOf(StoredPaymentMethod::class, $method);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());

        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setCustomer($method->getCustomer());
        $payment->setStoredPaymentMethod($method);
        $payum->getStorage(Payment::class)->update($payment);

        $result = $payum->getGateway($method->getGatewayName())->execute(CaptureCommand::forPayment($payment));

        $payum->getStorage(Payment::class)->update($payment);

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertFalse($result->isFailed());
    }

    public function testShouldChargeWithTheCustomerPresentAndAReason(): void
    {
        $payment = $this->savedPaymentWithMethod();
        $gateway = $this->payum->getGateway('offline');

        $result = $gateway->execute(CaptureCommand::forPayment($payment, initiator: Initiator::Customer));
        $authorized = $gateway->execute(AuthorizeCommand::forPayment($this->savedPaymentWithMethod(), initiator: Initiator::Customer, reason: ChargeReason::Installment));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(PaymentStatus::Authorized, $authorized->status);
    }

    public function testShouldRefuseAChargeOnAnUnsavedPayment(): void
    {
        $method = $this->payum->getStorage(StoredPaymentMethod::class)->find($this->savedMethodId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $method);

        // The file storage identifies a payment by its number, so a payment without one has not been saved.
        $payment = new Payment();
        $payment->setStoredPaymentMethod($method);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('save the payment before dispatching');

        $this->payum->getGateway('offline')->execute(CaptureCommand::forPayment($payment));
    }

    public function testShouldSaveAMethodWithoutTakingAPayment(): void
    {
        $payum = $this->payum;

        $method = new StoredPaymentMethod();
        $method->setGatewayName('offline');
        $method->setCustomer($payum->getStorage(Customer::class)->find('customer-42'));
        $payum->getStorage(StoredPaymentMethod::class)->update($method);

        $token = $payum->getTokenIssuer()->create(TokenPurpose::SavePaymentMethod, 'offline', $method, 'saved.php');
        $this->assertStringContainsString('save-payment-method.php', $token->getTargetUrl());

        $response = $payum->savePaymentMethod(Request::create($token->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('saved.php', $response->getTargetUrl());

        $token = $payum->getHttpRequestVerifier()->verify(Request::create($response->getTargetUrl()));
        $payum->getHttpRequestVerifier()->invalidate($token);

        $identity = $token->getDetails();
        $method = $payum->getStorage($identity->getClass())->find($identity);

        $this->assertInstanceOf(StoredPaymentMethod::class, $method);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
    }

    public function testShouldSaveAMethodFromCode(): void
    {
        $method = $this->newMethod();

        $result = $this->payum->getGateway('offline')->execute(SavePaymentMethodCommand::forMethod($method));

        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);

        $this->assertNull($result->status);
        $this->assertNull($result->next);
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
    }

    public function testShouldDeleteASavedMethod(): void
    {
        $method = $this->payum->getStorage(StoredPaymentMethod::class)->find($this->savedMethodId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $method);

        $result = $this->payum->getGateway($method->getGatewayName())->execute(DeletePaymentMethodCommand::forMethod($method));

        $this->assertFalse($result->isFailed());
        $this->assertSame(StoredPaymentMethodStatus::Revoked, $method->getStatus());

        $stored = $this->payum->getStorage(StoredPaymentMethod::class)->find($this->savedMethodIdOf($method));
        $this->assertInstanceOf(StoredPaymentMethod::class, $stored);
        $this->assertSame(StoredPaymentMethodStatus::Revoked, $stored->getStatus());
    }

    public function testShouldTellAListenerWhenAMethodChanges(): void
    {
        $mailer = new class() {
            /**
             * @var list<string>
             */
            public array $asked = [];

            public function askForNewPaymentMethod(?CustomerInterface $customer): void
            {
                $this->asked[] = (string) $customer?->getCustomerId();
            }
        };
        $dispatcher = $this->dispatcher;

        $dispatcher->addListener(PaymentMethodStatusChanged::class, function (PaymentMethodStatusChanged $event) use ($mailer): void {
            if (StoredPaymentMethodStatus::Expired === $event->to || StoredPaymentMethodStatus::Revoked === $event->to) {
                $mailer->askForNewPaymentMethod($event->method->getCustomer());
            }
        });

        $method = $this->newMethod();
        $this->payum->getGateway('offline')->execute(SavePaymentMethodCommand::forMethod($method));
        $this->assertSame([], $mailer->asked);

        $this->payum->getGateway('offline')->execute(DeletePaymentMethodCommand::forMethod($method));
        $this->assertSame(['customer-42'], $mailer->asked);
    }

    public function testAnUnknownStatusShouldReadAsPending(): void
    {
        $method = new StoredPaymentMethod();
        (fn () => $this->status = 'paused')->call($method);

        $this->assertSame(StoredPaymentMethodStatus::Pending, $method->getStatus());
    }

    private function newMethod(): StoredPaymentMethod
    {
        $method = new StoredPaymentMethod();
        $method->setGatewayName('offline');
        $method->setCustomer($this->payum->getStorage(Customer::class)->find('customer-42'));
        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);

        return $method;
    }

    private function savedMethodId(): mixed
    {
        $method = $this->newMethod();
        $this->payum->getGateway('offline')->execute(SavePaymentMethodCommand::forMethod($method));

        return $this->savedMethodIdOf($method);
    }

    private function savedMethodIdOf(StoredPaymentMethod $method): mixed
    {
        return $this->payum->getStorage(StoredPaymentMethod::class)->identify($method)->getId();
    }

    private function savedPaymentWithMethod(): Payment
    {
        $method = $this->payum->getStorage(StoredPaymentMethod::class)->find($this->savedMethodId());
        $this->assertInstanceOf(StoredPaymentMethod::class, $method);

        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setStoredPaymentMethod($method);
        $this->payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }
}
