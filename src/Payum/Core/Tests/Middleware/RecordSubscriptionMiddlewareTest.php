<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DateTimeImmutable;
use DI\Container;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Event\SubscriptionStatusChanged;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Legacy\UndescribedGateway;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Middleware\RecordSubscriptionMiddleware;
use Payum\Core\Model\Subscription;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Event\RecordingEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

final class RecordSubscriptionMiddlewareTest extends TestCase
{
    /**
     * @var list<object>
     */
    private array $saved = [];

    private InMemoryReferenceIndex $index;

    private RecordingEventDispatcher $events;

    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        $this->index = new InMemoryReferenceIndex();
        $this->events = new RecordingEventDispatcher();
        AcmeSubscriptionNotifyHandler::$reference = null;
    }

    public function testShouldRecordWhatCreateReturned(): void
    {
        $subscription = new Subscription();
        $end = new DateTimeImmutable('2026-12-20');

        $this->dispatch($subscription, SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1', $end)));

        $this->assertSame('sub_1', $subscription->getReference());
        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertSame($end, $subscription->getCurrentPeriodEnd());
        $this->assertContains($subscription, $this->saved);
    }

    public function testShouldIndexTheReference(): void
    {
        $this->dispatch(new Subscription(), SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1')));

        $record = $this->index->find('acme', 'sub_1');
        $this->assertNotNull($record);
        $this->assertSame(Subscription::class, $record->getIdentity()->getClass());
        $this->assertSame('42', $record->getIdentity()->getId());
    }

    public function testShouldAnnounceOnlyRealChanges(): void
    {
        $subscription = new Subscription();
        $result = SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1'));

        $this->dispatch($subscription, $result);
        $this->dispatch($subscription, $result);

        $events = $this->events->ofType(SubscriptionStatusChanged::class);
        $this->assertCount(1, $events);
        $this->assertSame(SubscriptionStatus::Pending, $events[0]->from);
        $this->assertSame(SubscriptionStatus::Active, $events[0]->to);
        $this->assertSame($subscription, $events[0]->subscription);
    }

    public function testShouldSaveNothingWhenTheSubscriptionHasNoStorage(): void
    {
        $subscription = new Subscription();

        try {
            $this->dispatch($subscription, SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1')), withStorage: false);
            $this->fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException) {
        }

        $this->assertSame([], $this->saved);
        $this->assertSame(SubscriptionStatus::Pending, $subscription->getStatus());
        $this->assertNull($subscription->getReference());
    }

    public function testShouldRefuseDetailsWithNowhereToGo(): void
    {
        $this->expectException(LogicException::class);

        $this->dispatch(null, SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1')));
    }

    public function testShouldLeaveAResultWithoutDetailsAlone(): void
    {
        $this->dispatch(new Subscription(), NotifyResult::ignored());

        $this->assertSame([], $this->saved);
        $this->assertSame([], $this->events->dispatched);
    }

    public function testAWebhookShouldMoveItToPastDue(): void
    {
        $index = new InMemoryReferenceIndex();
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setReferenceIndex($index)
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://payum.dev/webhooks/acme'))
            ->registerGateway('acme', new AcmeSubscriptionConfig())
            ->getPayum();
        $subscription = new Subscription();
        $storage = $payum->getStorage(Subscription::class);
        $storage->update($subscription);
        $payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));
        AcmeSubscriptionNotifyHandler::$reference = 'sub_1';

        $payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));

        $identity = $storage->identify($subscription);
        $reloaded = $storage->find($identity->getId());
        $this->assertInstanceOf(Subscription::class, $reloaded);
        $this->assertSame(SubscriptionStatus::PastDue, $reloaded->getStatus());
    }

    public function testShouldBeInTheDefaultPipeline(): void
    {
        $resolved = CoreGatewayFactory::defaultMiddleware()->resolve(
            new Container((new CoreGatewayFactory())->createConfig([]))
        );

        $this->assertContains(RecordSubscriptionMiddleware::class, array_map(static fn (object $m): string => $m::class, $resolved));
    }

    private function dispatch(?Subscription $subscription, SubscriptionResult|NotifyResult $result, bool $withStorage = true): void
    {
        $identity = $this->createMock(IdentityInterface::class);
        $identity->method('getId')->willReturn(42);
        $identity->method('getClass')->willReturn(Subscription::class);

        $storage = $this->createMock(StorageInterface::class);
        $storage->method('support')->willReturnCallback(static fn (object $model): bool => $model instanceof Subscription);
        $storage->method('identify')->willReturn($identity);
        $storage->method('update')->willReturnCallback(function (object $model): object {
            $this->saved[] = $model;

            return $model;
        });
        $registry = $this->createMock(StorageRegistryInterface::class);
        $registry->method('getStorages')->willReturn([$storage]);
        $registry->method('getStorage')->willReturnCallback(static function () use ($storage, $withStorage): StorageInterface {
            if (! $withStorage) {
                throw new InvalidArgumentException('No storage for the subscription.');
            }

            return $storage;
        });

        $command = $subscription instanceof Subscription
            ? CreateSubscriptionCommand::forSubscription($subscription)
            : CreateSubscriptionCommand::forToken($this->createMock(TokenInterface::class));

        (new Pipeline([new RecordSubscriptionMiddleware($this->index, $registry, $this->events)]))->process(
            $command,
            new Context(
                $this->createMock(Gateway::class),
                $command,
                new UndescribedGateway('acme'),
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(TokenIssuerInterface::class),
                $subscription,
                null,
                [],
                'acme',
            ),
            static fn (): SubscriptionResult|NotifyResult => $result,
        );
    }
}

final class AcmeSubscriptionConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeSubscriptionGateway::class;
    }
}

final class AcmeSubscriptionGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeSubscriptionConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeSubscriptionCreateHandler::class, AcmeSubscriptionNotifyHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Subscriptions';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmeSubscriptionCreateHandler implements CreateSubscriptionHandlerInterface
{
    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        return SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1'));
    }
}

final class AcmeSubscriptionNotifyHandler implements NotifyHandlerInterface
{
    public static ?string $reference = null;

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::handled()->withSubscription(new SubscriptionDetails(status: SubscriptionStatus::PastDue));
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::verified([], 'evt_1', 'invoice.payment_failed', null, self::$reference);
    }
}
