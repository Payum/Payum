<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Event\WebhookRejected;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Reference\MerchantReference;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\InMemoryStore;

/**
 * A webhook arriving on a route the application owns carries no token. The payment it is about is found
 * from what the message names, before it is locked and saved like one loaded from a token.
 */
final class WebhookSubjectResolutionTest extends TestCase
{
    private InMemoryReferenceIndex $index;

    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        $this->index = new InMemoryReferenceIndex();
        ResolvingNotifyHandler::$seen = null;
        ResolvingNotifyHandler::$handled = false;
        ResolvingNotifyHandler::$verifications = 0;
        ResolvingNotifyHandler::$forged = false;
        ResolvingCaptureHandler::$whilePending = null;
    }

    public function testShouldResolveTheSubjectFromTheMerchantReference(): void
    {
        $payum = $this->buildPayum();
        $payment = $this->savedPayment($payum);
        $this->recordMerchantReference($payment);

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertTrue(ResolvingNotifyHandler::$handled);
        $this->assertSame($payment, ResolvingNotifyHandler::$seen);
        $this->assertSame(1, ResolvingNotifyHandler::$verifications);
    }

    public function testShouldResolveTheSubjectFromAnIndexedPspReference(): void
    {
        $payum = $this->buildPayum(reference: 'pi_9');
        $payment = $this->savedPayment($payum);
        $this->index->record(ResolvingGateway::class, 'pi_9', 'acme', new Identity($payment->getNumber(), Payment::class));

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertSame($payment, ResolvingNotifyHandler::$seen);
    }

    public function testShouldResolveBeforeTheCreatingCommandReturned(): void
    {
        $payum = $this->buildPayum();
        $payment = $this->savedPayment($payum);

        // The PSP calls back while the capture that sent the merchant reference is still running, so the
        // capture's transaction id has not been indexed.
        ResolvingCaptureHandler::$whilePending = function (string $merchantReference) use ($payum): void {
            $this->assertNull($this->index->find(ResolvingGateway::class, 'pi_9'));

            $payum->getGateway('acme')->execute(NotifyCommand::forGateway()->withEvent(
                WebhookEvent::verified([], 'evt_1', 'payment.captured', merchantReference: $merchantReference),
            ));
        };

        $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $this->assertInstanceOf(PaymentInterface::class, ResolvingNotifyHandler::$seen);
        $this->assertSame($payment->getNumber(), ResolvingNotifyHandler::$seen->getNumber());
    }

    public function testShouldSaveTheResolvedSubject(): void
    {
        $payum = $this->buildPayum();
        $payment = $this->savedPayment($payum);
        $this->recordMerchantReference($payment);

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $reloaded = (new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))->find($payment->getNumber());

        $this->assertInstanceOf(Payment::class, $reloaded);
        $this->assertSame(PaymentStatus::Captured, $reloaded->getStatus());
    }

    public function testShouldLockTheResolvedSubject(): void
    {
        $locks = new RecordingLockFactory(new InMemoryStore());
        $payum = $this->buildPayum(locks: $locks);
        $payment = $this->savedPayment($payum);
        $this->recordMerchantReference($payment);

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertSame(['payum:' . Payment::class . ':' . $payment->getNumber()], $locks->resources);
    }

    public function testShouldRunWithNoSubjectWhenNothingMatches(): void
    {
        $payum = $this->buildPayum(reference: 'pi_unknown', merchantReference: 'py_unknown');

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertTrue(ResolvingNotifyHandler::$handled);
        $this->assertNull(ResolvingNotifyHandler::$seen);
        $this->assertNull($result->status);
    }

    public function testShouldNotVerifyTwiceWhenTheCommandCarriesAnEvent(): void
    {
        $payum = $this->buildPayum();
        $payment = $this->savedPayment($payum);
        $reference = $this->recordMerchantReference($payment);

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway()->withEvent(
            WebhookEvent::verified([], 'evt_1', 'payment.captured', merchantReference: $reference),
        ));

        $this->assertSame(0, ResolvingNotifyHandler::$verifications);
        $this->assertSame($payment, ResolvingNotifyHandler::$seen);
    }

    public function testShouldLoadTheResolvedSubjectOnlyOnceItIsLocked(): void
    {
        $log = new ResolutionLog();
        $payments = new CopyingPaymentStorage($log);
        $payum = $this->buildPayum(locks: new RecordingLockFactory(new InMemoryStore(), $log), payments: $payments);
        $payment = $this->savedPayment($payum);
        $this->recordMerchantReference($payment);

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        // Found before the lock to know there is something to lock, read again once it is held.
        $this->assertSame(['find', 'lock', 'find'], $log->entries);
        $this->assertSame($payments->last, ResolvingNotifyHandler::$seen);
    }

    public function testShouldRefuseAForgedWebhookWithoutHandlingIt(): void
    {
        ResolvingNotifyHandler::$forged = true;
        $events = new ResolutionEvents();
        $payum = $this->buildPayum(events: $events);

        try {
            $payum->getGateway('acme')->execute(NotifyCommand::forGateway());
            $this->fail('Expected WebhookNotVerifiedException.');
        } catch (WebhookNotVerifiedException) {
        }

        $rejected = array_filter($events->seen, static fn (object $e): bool => $e instanceof WebhookRejected);
        $this->assertCount(1, $rejected);
        $this->assertFalse(ResolvingNotifyHandler::$handled);
    }

    public function testShouldFallThroughToThePspReferenceWhenTheMerchantReferenceNamesNothingStored(): void
    {
        $payum = $this->buildPayum(reference: 'pi_9');
        $payment = $this->savedPayment($payum);
        $gone = new Identity(uniqid('gone', true), Payment::class);
        $this->index->record(MerchantReference::NAMESPACE, MerchantReference::of($gone), 'acme', $gone);
        ResolvingNotifyHandler::$merchantReference = MerchantReference::of($gone);
        $this->index->record(ResolvingGateway::class, 'pi_9', 'acme', new Identity($payment->getNumber(), Payment::class));

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertSame($payment, ResolvingNotifyHandler::$seen);
    }

    public function testShouldRunWithNoSubjectWhenTheEventNamesNoReference(): void
    {
        $payum = $this->buildPayum();
        $this->savedPayment($payum);

        $payum->getGateway('acme')->execute(NotifyCommand::forGateway());

        $this->assertTrue(ResolvingNotifyHandler::$handled);
        $this->assertNull(ResolvingNotifyHandler::$seen);
    }

    public function testRetryShouldAnswer503(): void
    {
        $retry = NotifyResult::retry();

        $this->assertTrue($retry->isRetry());
        $this->assertNull($retry->status);
        $this->assertInstanceOf(Acknowledgement::class, $retry->acknowledgement);
        $this->assertSame(503, $retry->acknowledgement->status);
        $this->assertSame(429, NotifyResult::retry(new Acknowledgement(429))->acknowledgement?->status);
        $this->assertFalse(NotifyResult::ignored()->isRetry());
    }

    /**
     * @param Payum<object> $payum
     */
    private function savedPayment(Payum $payum): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid('', true));
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setStatus(PaymentStatus::Pending);
        $payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }

    private function recordMerchantReference(Payment $payment): string
    {
        $identity = new Identity($payment->getNumber(), Payment::class);
        $reference = MerchantReference::of($identity);
        $this->index->record(MerchantReference::NAMESPACE, $reference, 'acme', $identity);
        ResolvingNotifyHandler::$merchantReference = $reference;

        return $reference;
    }

    /**
     * @param FilesystemStorage<object>|null $payments
     *
     * @return Payum<object>
     */
    private function buildPayum(
        ?string $reference = null,
        ?string $merchantReference = null,
        ?LockFactory $locks = null,
        ?FilesystemStorage $payments = null,
        ?EventDispatcherInterface $events = null,
    ): Payum {
        ResolvingNotifyHandler::$reference = $reference;
        ResolvingNotifyHandler::$merchantReference = $merchantReference;

        $builder = new PayumBuilder();

        if ($payments instanceof FilesystemStorage) {
            $builder->addStorage(Payment::class, $payments);
        }

        if ($events instanceof EventDispatcherInterface) {
            $builder->addGlobalService(EventDispatcherInterface::class, $events);
        }

        $builder
            ->addDefaultStorages()
            ->setReferenceIndex($this->index)
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://payum.dev/webhooks/acme'));

        if ($locks instanceof LockFactory) {
            $builder
                ->addGlobalService(LockFactory::class, $locks)
                ->addGlobalService(LockingMiddleware::class, static fn (ContainerInterface $c): LockingMiddleware => new LockingMiddleware(
                    $c->get(LockFactory::class),
                    $c->get(StorageRegistryInterface::class),
                ))
                ->addMiddleware(LockingMiddleware::class);
        }

        return $builder
            ->registerGateway('acme', new ResolvingConfig())
            ->getPayum();
    }
}

final class ResolvingConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return ResolvingGateway::class;
    }
}

final class ResolvingGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return ResolvingConfig::class;
    }

    public function handlers(): array
    {
        return [ResolvingNotifyHandler::class, ResolvingCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Resolving';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class ResolvingNotifyHandler implements NotifyHandlerInterface
{
    public static ?string $reference = null;

    public static ?string $merchantReference = null;

    public static ?PaymentInterface $seen = null;

    public static bool $handled = false;

    public static int $verifications = 0;

    public static bool $forged = false;

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        self::$handled = true;
        self::$seen = $context->payment();

        return null === self::$seen ? NotifyResult::ignored() : NotifyResult::handled(PaymentStatus::Captured);
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        ++self::$verifications;

        if (self::$forged) {
            throw new WebhookNotVerifiedException('The signature does not match.');
        }

        return WebhookEvent::verified([], 'evt_1', 'payment.captured', null, self::$reference, self::$merchantReference);
    }
}

final class ResolvingCaptureHandler implements CaptureHandlerInterface
{
    /**
     * @var (callable(string): void)|null
     */
    public static $whilePending;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        if (null !== self::$whilePending) {
            (self::$whilePending)($context->merchantReference());
        }

        return CaptureResult::captured('pi_9');
    }
}

final class RecordingLockFactory extends LockFactory
{
    /**
     * @var list<string>
     */
    public array $resources = [];

    public function __construct(
        PersistingStoreInterface $store,
        private readonly ?ResolutionLog $log = null,
    ) {
        parent::__construct($store);
    }

    public function createLock(string $resource, ?float $ttl = 300.0, bool $autoRelease = true): LockInterface
    {
        $this->resources[] = $resource;

        if ($this->log instanceof ResolutionLog) {
            $this->log->entries[] = 'lock';
        }

        return parent::createLock($resource, $ttl, $autoRelease);
    }
}

final class ResolutionLog
{
    /**
     * @var list<string>
     */
    public array $entries = [];
}

/**
 * Hands out a fresh copy on every read, as a storage without an identity map does.
 *
 * @extends FilesystemStorage<object>
 */
final class CopyingPaymentStorage extends FilesystemStorage
{
    public ?object $last = null;

    public function __construct(
        private readonly ResolutionLog $log
    ) {
        parent::__construct(sys_get_temp_dir(), Payment::class, 'number');
    }

    protected function doFind(mixed $id): ?object
    {
        $this->log->entries[] = 'find';
        $model = parent::doFind($id);

        return $this->last = null === $model ? null : clone $model;
    }
}

final class ResolutionEvents implements EventDispatcherInterface
{
    /**
     * @var list<object>
     */
    public array $seen = [];

    public function dispatch(object $event): object
    {
        $this->seen[] = $event;

        return $event;
    }
}
