<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DI\Container;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Event\PaymentMethodStatusChanged;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\UndescribedGateway;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Middleware\RecordStoredPaymentMethodMiddleware;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Event\RecordingEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class RecordStoredPaymentMethodMiddlewareTest extends TestCase
{
    /**
     * @var list<object>
     */
    private array $saved = [];

    private InMemoryReferenceIndex $index;

    private RecordingEventDispatcher $events;

    protected function setUp(): void
    {
        $this->index = new InMemoryReferenceIndex();
        $this->events = new RecordingEventDispatcher();
    }

    public function testShouldRecordTheMethodACaptureSaved(): void
    {
        $method = new StoredPaymentMethod();
        $payment = $this->paymentWith($method);

        $this->dispatch($payment, CaptureResult::captured('pi_1')->withStoredPaymentMethod(
            StoredPaymentMethodDetails::active('pm_1', type: PaymentMethodType::Card, last4: '4242'),
        ));

        $this->assertSame('pm_1', $method->getReference());
        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertSame('4242', $method->getLast4());
        $this->assertContains($method, $this->saved);
    }

    public function testShouldSetTheCustomersGatewayId(): void
    {
        $customer = new Customer();
        $method = new StoredPaymentMethod();
        $method->setCustomer($customer);

        $this->dispatch($this->paymentWith($method), CaptureResult::captured('pi_1')->withStoredPaymentMethod(
            StoredPaymentMethodDetails::active('pm_1', gatewayCustomerId: 'cus_1'),
        ));

        $this->assertSame('cus_1', $customer->getGatewayCustomerId('acme'));
        $this->assertContains($customer, $this->saved);
    }

    public function testShouldNotSaveACustomerWhoseGatewayIdIsUnchanged(): void
    {
        $customer = new Customer();
        $customer->setGatewayCustomerId('acme', 'cus_1');
        $method = new StoredPaymentMethod();
        $method->setCustomer($customer);

        $this->dispatch($this->paymentWith($method), CaptureResult::captured('pi_1')->withStoredPaymentMethod(
            StoredPaymentMethodDetails::active('pm_1', gatewayCustomerId: 'cus_1'),
        ));

        $this->assertNotContains($customer, $this->saved);
    }

    public function testShouldIndexTheMethodsReference(): void
    {
        $this->dispatch($this->paymentWith(new StoredPaymentMethod()), CaptureResult::captured('pi_1')->withStoredPaymentMethod(
            StoredPaymentMethodDetails::active('pm_1'),
        ));

        $record = $this->index->find('acme', 'pm_1');
        $this->assertNotNull($record);
        $this->assertSame(StoredPaymentMethod::class, $record->getIdentity()->getClass());
        $this->assertSame('42', $record->getIdentity()->getId());
    }

    public function testShouldAnnounceAStatusChangeOnce(): void
    {
        $payment = $this->paymentWith(new StoredPaymentMethod());
        $result = CaptureResult::captured('pi_1')->withStoredPaymentMethod(StoredPaymentMethodDetails::active('pm_1'));

        $this->dispatch($payment, $result);
        $this->dispatch($payment, $result);

        $events = $this->events->ofType(PaymentMethodStatusChanged::class);
        $this->assertCount(1, $events);
        $this->assertSame(StoredPaymentMethodStatus::Pending, $events[0]->from);
        $this->assertSame(StoredPaymentMethodStatus::Active, $events[0]->to);
        $this->assertSame($payment->getStoredPaymentMethod(), $events[0]->method);
    }

    public function testShouldRefuseDetailsWithNowhereToGo(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('give the payment a StoredPaymentMethod before dispatching');

        $this->dispatch(new Payment(), CaptureResult::captured('pi_1')->withStoredPaymentMethod(
            StoredPaymentMethodDetails::active('pm_1'),
        ));
    }

    public function testShouldLeaveAResultWithoutDetailsAlone(): void
    {
        $this->dispatch(new Payment(), CaptureResult::captured('pi_1'));

        $this->assertSame([], $this->saved);
        $this->assertSame([], $this->events->dispatched);
    }

    public function testShouldBeInTheDefaultPipeline(): void
    {
        $resolved = CoreGatewayFactory::defaultMiddleware()->resolve(
            new Container((new CoreGatewayFactory())->createConfig([]))
        );

        $this->assertContains(RecordStoredPaymentMethodMiddleware::class, array_map(static fn (object $m): string => $m::class, $resolved));
    }

    private function paymentWith(StoredPaymentMethod $method): Payment
    {
        $payment = new Payment();
        $payment->setStoredPaymentMethod($method);

        return $payment;
    }

    private function dispatch(Payment $payment, CaptureResult $result): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $identity->method('getId')->willReturn(42);
        $identity->method('getClass')->willReturn(StoredPaymentMethod::class);

        $storage = $this->createMock(StorageInterface::class);
        $storage->method('support')->willReturnCallback(static fn (object $model): bool => $model instanceof StoredPaymentMethod);
        $storage->method('identify')->willReturn($identity);
        $storage->method('update')->willReturnCallback(function (object $model): object {
            $this->saved[] = $model;

            return $model;
        });
        $registry = $this->createMock(StorageRegistryInterface::class);
        $registry->method('getStorages')->willReturn([$storage]);
        $registry->method('getStorage')->willReturn($storage);

        $command = CaptureCommand::forToken($this->createMock(TokenInterface::class));

        (new Pipeline([new RecordStoredPaymentMethodMiddleware($this->index, $registry, $this->events)]))->process(
            $command,
            new Context(
                $this->createMock(Gateway::class),
                $command,
                new UndescribedGateway('acme'),
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(TokenIssuerInterface::class),
                $payment,
                null,
                [],
                'acme',
            ),
            static fn (): CaptureResult => $result,
        );
    }
}
