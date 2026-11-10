<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\ConcurrentOperationException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\InMemoryStore;

/**
 * Two Payum instances sharing one lock store and one storage directory stand in for two PHP processes:
 * the customer's return from the PSP and a double click, or a webhook racing the return URL.
 */
final class ConcurrentOperationTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        LockingCaptureHandler::$charges = 0;
        LockingCaptureHandler::$during = null;
    }

    public function testShouldChargeOnceWhenTheSamePaymentIsCapturedTwiceAtOnce(): void
    {
        $locks = new LockFactory(new InMemoryStore());
        $first = $this->buildPayum($locks);
        $second = $this->buildPayum($locks);
        $token = $this->captureToken($first);
        $refused = null;

        LockingCaptureHandler::$during = static function () use ($second, $token, &$refused): void {
            try {
                $second->getGateway('acme')->execute(CaptureCommand::forToken($token));
            } catch (ConcurrentOperationException $exception) {
                $refused = $exception;
            }
        };

        $first->getGateway('acme')->execute(CaptureCommand::forToken($token));

        $this->assertInstanceOf(ConcurrentOperationException::class, $refused);
        $this->assertSame(1, LockingCaptureHandler::$charges);
    }

    public function testShouldLoadThePaymentOnlyOnceTheLockIsHeld(): void
    {
        $log = new CallLog();
        $payum = $this->buildPayum(new LockFactory(new LoggingLockStore($log)), new LoggingPaymentStorage($log));
        $token = $this->captureToken($payum);
        $log->entries = [];

        $payum->getGateway('acme')->execute(CaptureCommand::forToken($token));

        $this->assertSame(['lock', 'find'], array_slice($log->entries, 0, 2));
    }

    public function testShouldLoadThePaymentOnlyOnceTheLockIsHeldForA1xRequestToo(): void
    {
        $log = new CallLog();
        $payum = $this->buildPayum(new LockFactory(new LoggingLockStore($log)), new LoggingPaymentStorage($log));
        $token = $this->captureToken($payum);
        $log->entries = [];

        $payum->getGateway('acme')->execute(new Capture($token));

        $this->assertSame(['lock', 'find'], array_slice($log->entries, 0, 2));
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(LockFactory $locks, ?LoggingPaymentStorage $payments = null): Payum
    {
        $builder = new PayumBuilder();

        if ($payments instanceof LoggingPaymentStorage) {
            $builder->addStorage(Payment::class, $payments);
        }

        // The default storages write to the system temporary directory, which both instances share.
        return $builder
            ->addDefaultStorages()
            ->addGlobalService(LockFactory::class, $locks)
            ->addGlobalService(LockingMiddleware::class, static fn (ContainerInterface $c): LockingMiddleware => new LockingMiddleware(
                $c->get(LockFactory::class),
                $c->get(StorageRegistryInterface::class),
                timeout: 0.0,
            ))
            ->addMiddleware(LockingMiddleware::class)
            ->registerGateway('acme', new LockingAcmeConfig())
            ->getPayum();
    }

    /**
     * @param Payum<object> $payum
     */
    private function captureToken(Payum $payum): TokenInterface
    {
        $payment = new Payment();
        $payment->setTotalAmount(1000);
        $payment->setCurrencyCode('USD');
        $payum->getStorage(Payment::class)->update($payment);

        return $payum->getTokenFactory()->createCaptureToken('acme', $payment, 'https://payum.dev/done');
    }
}

final class CallLog
{
    /**
     * @var list<string>
     */
    public array $entries = [];
}

final class LoggingLockStore implements PersistingStoreInterface
{
    private readonly InMemoryStore $store;

    public function __construct(
        private readonly CallLog $log
    ) {
        $this->store = new InMemoryStore();
    }

    public function save(Key $key): void
    {
        $this->log->entries[] = 'lock';
        $this->store->save($key);
    }

    public function delete(Key $key): void
    {
        $this->store->delete($key);
    }

    public function exists(Key $key): bool
    {
        return $this->store->exists($key);
    }

    public function putOffExpiration(Key $key, float $ttl): void
    {
        $this->store->putOffExpiration($key, $ttl);
    }
}

/**
 * @extends FilesystemStorage<object>
 */
final class LoggingPaymentStorage extends FilesystemStorage
{
    public function __construct(
        private readonly CallLog $log
    ) {
        parent::__construct(sys_get_temp_dir(), Payment::class, 'number');
    }

    protected function doFind(mixed $id): ?object
    {
        $this->log->entries[] = 'find';

        return parent::doFind($id);
    }
}

final class LockingAcmeConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return LockingAcmeGateway::class;
    }
}

final class LockingAcmeGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return LockingAcmeConfig::class;
    }

    public function handlers(): array
    {
        return [LockingCaptureHandler::class];
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

final class LockingCaptureHandler implements CaptureHandlerInterface
{
    public static int $charges = 0;

    /**
     * @var (callable(): void)|null
     */
    public static $during;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        if ($state['charge_id']) {
            return CaptureResult::captured($state['charge_id']);
        }

        if (null !== self::$during) {
            (self::$during)();
        }

        ++self::$charges;
        $state['charge_id'] = 'ch_' . self::$charges;

        return CaptureResult::captured($state['charge_id']);
    }
}
