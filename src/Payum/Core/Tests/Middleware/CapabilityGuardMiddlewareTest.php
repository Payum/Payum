<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use ArrayObject;
use Closure;
use DI\Container;
use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\Initiator;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedOperationException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\UndescribedGateway;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\CapabilityGuardMiddleware;
use Payum\Core\Middleware\IdempotencyMiddleware;
use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\InMemoryStore;

final class CapabilityGuardMiddlewareTest extends TestCase
{
    public function testShouldPassAChargeWithNoStoredMethod(): void
    {
        $payment = new Payment();

        $this->assertTrue($this->charge(CaptureCommand::forPayment($payment), new GuardedGateway(), $payment));
    }

    public function testShouldRefuseAStoredMethodChargeOnAPaymentThatWasNeverSaved(): void
    {
        $payment = $this->paymentWith('pm_1');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('save the payment before dispatching a charge on a stored payment method');

        $this->charge(CaptureCommand::forPayment($payment), new GuardedGateway([Capability::Recurring]), $payment, saved: false);
    }

    public function testShouldLetAGatewayThatDeclaresNoCapabilitiesThrough(): void
    {
        $payment = $this->paymentWith(null);

        $this->assertTrue($this->charge(CaptureCommand::forPayment($payment), new UndescribedGateway('acme'), $payment));
    }

    public function testShouldRefuseToSaveAMethodOnAGatewayWithoutStoredPaymentMethods(): void
    {
        $payment = $this->paymentWith(null);

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Gateway "acme" does not declare Capability::StoredPaymentMethods');

        $this->charge(CaptureCommand::forPayment($payment, initiator: Initiator::Customer), new GuardedGateway([Capability::Recurring]), $payment);
    }

    public function testShouldSaveAMethodOnAGatewayWithStoredPaymentMethods(): void
    {
        $payment = $this->paymentWith(null);

        $this->assertTrue($this->charge(
            CaptureCommand::forPayment($payment, initiator: Initiator::Customer),
            new GuardedGateway([Capability::StoredPaymentMethods]),
            $payment,
        ));
    }

    public function testShouldRefuseAMerchantChargeOnAGatewayWithoutRecurring(): void
    {
        $payment = $this->paymentWith('pm_1');

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Gateway "acme" does not declare Capability::Recurring');

        $this->charge(CaptureCommand::forPayment($payment), new GuardedGateway([Capability::StoredPaymentMethods]), $payment);
    }

    public function testShouldPassAMerchantChargeOnAGatewayWithRecurring(): void
    {
        $payment = $this->paymentWith('pm_1');

        $this->assertTrue($this->charge(CaptureCommand::forPayment($payment), new GuardedGateway([Capability::Recurring]), $payment));
    }

    public function testShouldPassACustomerChargeOnASavedMethodWithNothingDeclared(): void
    {
        $payment = $this->paymentWith('pm_1');

        $this->assertTrue($this->charge(CaptureCommand::forPayment($payment, initiator: Initiator::Customer), new GuardedGateway(), $payment));
    }

    public function testShouldLeaveACommandThatDoesNotChargeAlone(): void
    {
        $payment = $this->paymentWith('pm_1');

        $this->assertTrue($this->charge(new RefundCommand(payment: $payment), new GuardedGateway(), $payment, saved: false));
    }

    public function testShouldReadTheStoredMethodOnlyOnceThePaymentIsLocked(): void
    {
        /** @var ArrayObject<int, string> $log */
        $log = new ArrayObject();
        $locks = new class(new InMemoryStore(), $log) extends LockFactory {
            /**
             * @param ArrayObject<int, string> $log
             */
            public function __construct(
                InMemoryStore $store,
                private readonly ArrayObject $log
            ) {
                parent::__construct($store);
            }

            public function createLock(string $resource, ?float $ttl = 300.0, bool $autoRelease = true): LockInterface
            {
                $this->log[] = 'lock';

                return parent::createLock($resource, $ttl, $autoRelease);
            }
        };
        $storages = $this->storages(true);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getDetails')->willReturn(new Identity(7, Payment::class));
        $command = CaptureCommand::forToken($token);

        $resolved = CoreGatewayFactory::defaultMiddleware()
            ->with(new LockingMiddleware($locks, $storages, 0.0))
            ->resolve(new Container((new CoreGatewayFactory())->createConfig([])));
        $classes = array_map(static fn (object $m): string => $m::class, $resolved);
        $guard = array_search(CapabilityGuardMiddleware::class, $classes, true);

        $this->assertSame(LockingMiddleware::class, $classes[$guard - 1] ?? null);
        $this->assertSame(IdempotencyMiddleware::class, $classes[$guard + 1] ?? null);

        (new Pipeline([new LockingMiddleware($locks, $storages, 0.0), new CapabilityGuardMiddleware($storages)]))->process(
            $command,
            $this->context($command, new GuardedGateway(), function () use ($log): Payment {
                $log[] = 'load';

                return $this->paymentWith('pm_1');
            }, $token),
            static fn (): CaptureResult => CaptureResult::captured(),
        );

        $this->assertSame(['lock', 'load'], $log->getArrayCopy());
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function charge(CommandInterface $command, PaymentGateway $gateway, Payment $payment, bool $saved = true): bool
    {
        $handled = false;

        (new Pipeline([new CapabilityGuardMiddleware($this->storages($saved))]))->process(
            $command,
            $this->context($command, $gateway, $payment),
            static function () use (&$handled): Result {
                $handled = true;

                return CaptureResult::captured();
            },
        );

        return $handled;
    }

    /**
     * @param CommandInterface<Result> $command
     * @param SubjectInterface|(Closure(): ?SubjectInterface) $subject
     */
    private function context(CommandInterface $command, PaymentGateway $gateway, SubjectInterface|Closure $subject, ?TokenInterface $token = null): Context
    {
        return new Context(
            $this->createMock(Gateway::class),
            $command,
            $gateway,
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(TokenIssuerInterface::class),
            $subject,
            $token,
            [],
            'acme',
        );
    }

    /**
     * @return StorageRegistryInterface<object>
     */
    private function storages(bool $saved): StorageRegistryInterface
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('support')->willReturn(true);
        $storage->method('identify')->willReturn(new Identity($saved ? 7 : '', Payment::class));
        $registry = $this->createMock(StorageRegistryInterface::class);
        $registry->method('getStorages')->willReturn([$storage]);

        return $registry;
    }

    private function paymentWith(?string $reference): Payment
    {
        $method = new StoredPaymentMethod();

        if (null !== $reference) {
            $method->apply(new StoredPaymentMethodDetails(reference: $reference));
        }

        $payment = new Payment();
        $payment->setStoredPaymentMethod($method);

        return $payment;
    }
}

final class GuardedGateway implements PaymentGateway, DeclaresCapabilities
{
    /**
     * @param list<Capability> $capabilities
     */
    public function __construct(
        private readonly array $capabilities = [],
    ) {
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function configClass(): string
    {
        return GuardedConfig::class;
    }

    public function handlers(): array
    {
        return [];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class GuardedConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return GuardedGateway::class;
    }
}
