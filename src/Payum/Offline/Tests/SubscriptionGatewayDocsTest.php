<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use DateTimeImmutable;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Money\Currency;
use Money\Money;
use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\SyncSubscriptionCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Event\SubscriptionPaymentFailed;
use Payum\Core\Event\SubscriptionPaymentSucceeded;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Handler\CancelSubscriptionHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\SyncSubscriptionHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Subscription;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Security\TokenPurpose;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Runs the handlers of docs/gateways/subscriptions.md, and the renewal setup of docs/subscriptions.md,
 * against a fake PSP.
 */
final class SubscriptionGatewayDocsTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $server;

    /**
     * @var Payum<object>
     */
    private Payum $payum;

    /**
     * @var list<SubscriptionPaymentSucceeded|SubscriptionPaymentFailed>
     */
    private array $events = [];

    protected function setUp(): void
    {
        $this->server = $_SERVER;
        $_SERVER['HTTP_HOST'] = 'shop.example.com';
        DocsSubscriptionApi::reset();

        $this->payum = $this->buildPayum(new PayumBuilder());
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
    }

    public function testShouldDeclareTheCapabilities(): void
    {
        $gateway = $this->payum->getGateway('acme');
        $this->assertInstanceOf(Gateway::class, $gateway);

        foreach ([Capability::CreateSubscription, Capability::CancelSubscription, Capability::SyncSubscription, Capability::CancelAtPeriodEnd, Capability::Webhooks] as $capability) {
            $this->assertContains($capability, $gateway->capabilities());
        }
    }

    public function testShouldCreateTheSubscriptionAndRecordTheFirstCharge(): void
    {
        $subscription = $this->subscription();

        $result = $this->payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        $this->assertNull($result->status);
        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertSame('sub_1', $subscription->getReference());
        $this->assertEquals(new DateTimeImmutable('@1798000000'), $subscription->getCurrentPeriodEnd());
        $this->assertSame(1000, DocsSubscriptionApi::$requests[0]['amount']);
        $this->assertSame('month', DocsSubscriptionApi::$requests[0]['interval']);
        $this->assertArrayHasKey('payum_reference', DocsSubscriptionApi::$requests[0]['metadata']);

        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(SubscriptionPaymentSucceeded::class, $this->events[0]);
        $this->assertTrue($this->events[0]->isFirst);
        $this->assertInstanceOf(Payment::class, $this->events[0]->payment);
        $this->assertSame(PaymentStatus::Captured, $this->events[0]->payment->getStatus());
    }

    public function testShouldUseThePlanReference(): void
    {
        $subscription = $this->subscription();
        $subscription->setPlanReference('price_pro');

        $this->payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        $this->assertSame('price_pro', DocsSubscriptionApi::$requests[0]['plan']);
        $this->assertArrayNotHasKey('amount', DocsSubscriptionApi::$requests[0]);
    }

    public function testShouldFailWithoutAPlanOrAnAmount(): void
    {
        $subscription = $this->subscription();
        $subscription->setTotalAmount(null);

        $result = $this->payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(FailureReason::Configuration, $result->failure?->reason);
        $this->assertSame(SubscriptionStatus::Pending, $subscription->getStatus());
    }

    public function testShouldCreateTheSubscriptionAcrossTheApprovalRedirect(): void
    {
        DocsSubscriptionApi::$approveUrl = 'https://acme.test/approve/sub_1';
        $token = $this->payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'acme', $this->subscription(), 'subscribed.php');

        $first = $this->payum->subscribe(Request::create($token->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $first);
        $this->assertSame('https://acme.test/approve/sub_1', $first->getTargetUrl());
        $this->assertSame($token->getTargetUrl(), DocsSubscriptionApi::$requests[0]['return_url']);

        $second = $this->payum->subscribe(Request::create($token->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $second);
        $this->assertSame($token->getAfterUrl(), $second->getTargetUrl());
        $this->assertCount(1, DocsSubscriptionApi::$requests);
    }

    public function testShouldCancelNowAndAtThePeriodEnd(): void
    {
        $subscription = $this->activeSubscription();
        $gateway = $this->payum->getGateway('acme');

        $gateway->execute(CancelSubscriptionCommand::forSubscription($subscription, atPeriodEnd: true));

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertEquals($subscription->getCurrentPeriodEnd(), $subscription->getCancelAt());
        $this->assertSame([
            'cancel' => 'sub_1',
            'at_period_end' => true,
        ], DocsSubscriptionApi::$requests[1]);

        $gateway->execute(CancelSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(SubscriptionStatus::Canceled, $subscription->getStatus());
        $this->assertEquals(new DateTimeImmutable('@1797000000'), $subscription->getCanceledAt());
    }

    public function testShouldSyncTheSubscription(): void
    {
        $subscription = $this->activeSubscription();
        DocsSubscriptionApi::$status = 'past_due';

        $result = $this->payum->getGateway('acme')->execute(SyncSubscriptionCommand::forSubscription($subscription));

        $this->assertInstanceOf(SubscriptionResult::class, $result);
        $this->assertSame(SubscriptionStatus::PastDue, $result->subscription()?->status);
        $this->assertSame(SubscriptionStatus::PastDue, $subscription->getStatus());
    }

    public function testAWebhookShouldRecordEachRenewalOnce(): void
    {
        $subscription = $this->activeSubscription();
        $this->events = [];

        $this->notify('invoice.payment_failed', 'in_2');

        $this->assertSame(SubscriptionStatus::PastDue, $subscription->getStatus());
        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(SubscriptionPaymentFailed::class, $this->events[0]);
        $this->assertSame(FailureReason::Declined, $this->events[0]->failure?->reason);
        $this->assertFalse($this->events[0]->isFirst);
        $renewal = $this->events[0]->payment;

        $this->notify('invoice.payment_failed', 'in_2');
        $this->assertCount(1, $this->events);

        $this->notify('invoice.paid', 'in_2');

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertCount(2, $this->events);
        $this->assertInstanceOf(SubscriptionPaymentSucceeded::class, $this->events[1]);
        $this->assertSame($renewal, $this->events[1]->payment);
        $this->assertInstanceOf(Payment::class, $renewal);
        $this->assertSame(PaymentStatus::Captured, $renewal->getStatus());
        $this->assertSame($subscription, $renewal->getSubscription());
    }

    public function testRenewalsShouldUseTheApplicationsPaymentClass(): void
    {
        $builder = (new PayumBuilder())
            ->addStorage(DocsRenewalPayment::class, new FilesystemStorage(sys_get_temp_dir(), DocsRenewalPayment::class, 'number'));
        $builder->setRenewalPaymentClass(DocsRenewalPayment::class);
        $this->payum = $this->buildPayum($builder);

        $this->payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($this->subscription()));

        $this->assertInstanceOf(DocsRenewalPayment::class, $this->events[0]->payment);
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(PayumBuilder $builder): Payum
    {
        $dispatcher = new EventDispatcher();
        $record = function (SubscriptionPaymentSucceeded|SubscriptionPaymentFailed $event): void {
            $this->events[] = $event;
        };
        $dispatcher->addListener(SubscriptionPaymentSucceeded::class, $record);
        $dispatcher->addListener(SubscriptionPaymentFailed::class, $record);
        $this->events = [];

        return $builder
            ->addDefaultStorages()
            ->setReferenceIndex(new InMemoryReferenceIndex())
            ->addGlobalService(EventDispatcherInterface::class, $dispatcher)
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://shop.example.com/webhooks/acme'))
            ->registerGateway('acme', new DocsSubscriptionConfig())
            ->getPayum();
    }

    private function notify(string $type, string $invoiceId): void
    {
        DocsSubscriptionApi::$webhook = [
            'id' => 'evt_' . uniqid(),
            'type' => $type,
            'data' => [
                'id' => $invoiceId,
                'subscription' => 'sub_1',
                'amount' => 1000,
                'currency' => 'EUR',
                'period_end' => 1800000000,
                'decline_code' => 'card_declined',
                'metadata' => [],
            ],
        ];

        $this->payum->getGateway('acme')->execute(NotifyCommand::forGateway());
    }

    private function subscription(): Subscription
    {
        $customer = new Customer();
        $customer->setCustomerId('customer-42');
        $customer->setGatewayCustomerId('acme', 'cus_1');
        $this->payum->getStorage(Customer::class)->update($customer);

        $subscription = new Subscription();
        $subscription->setGatewayName('acme');
        $subscription->setCustomer($customer);
        $subscription->setTotalAmount(1000);
        $subscription->setCurrencyCode('EUR');
        $subscription->setInterval(BillingInterval::fromString('1 month'));
        $this->payum->getStorage(Subscription::class)->update($subscription);

        return $subscription;
    }

    private function activeSubscription(): Subscription
    {
        $subscription = $this->subscription();
        $this->payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        return $subscription;
    }
}

class DocsRenewalPayment extends Payment
{
}

final class DocsSubscriptionApi
{
    /**
     * @var list<array<string, mixed>>
     */
    public static array $requests = [];

    public static ?string $approveUrl = null;

    public static string $status = 'active';

    /**
     * @var array<string, mixed>
     */
    public static array $webhook = [];

    public static function reset(): void
    {
        self::$requests = [];
        self::$approveUrl = null;
        self::$status = 'active';
        self::$webhook = [];
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function createSubscription(array $request): array
    {
        self::$requests[] = $request;

        return [
            'id' => 'sub_1',
            'approve_url' => self::$approveUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function findSubscription(string $id): array
    {
        return [
            'id' => $id,
            'status' => self::$status,
            'current_period_end' => 1798000000,
            'latest_invoice' => [
                'id' => 'in_1',
                'amount' => 1000,
                'currency' => 'EUR',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelSubscription(string $id, bool $atPeriodEnd): array
    {
        self::$requests[] = [
            'cancel' => $id,
            'at_period_end' => $atPeriodEnd,
        ];

        return [
            'id' => $id,
            'canceled_at' => 1797000000,
        ];
    }
}

final class DocsSubscriptionConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return DocsSubscriptionGateway::class;
    }
}

final class DocsSubscriptionGateway implements GatewayInterface, DeclaresCapabilities
{
    public function handlers(): array
    {
        return [
            DocsCreateSubscriptionHandler::class,
            DocsCancelSubscriptionHandler::class,
            DocsSyncSubscriptionHandler::class,
            DocsSubscriptionNotifyHandler::class,
        ];
    }

    public function capabilities(): array
    {
        return [Capability::CancelAtPeriodEnd];
    }

    public function configClass(): string
    {
        return DocsSubscriptionConfig::class;
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

final class DocsCreateSubscriptionHandler implements CreateSubscriptionHandlerInterface
{
    public function __construct(
        private readonly DocsSubscriptionApi $api,
    ) {
    }

    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $subscription = $context->subscription();
        $state = $context->state();

        if (! isset($state['subscription_id'])) {
            $request = [
                'customer' => $context->customer()?->getGatewayCustomerId((string) $context->gatewayName()),
                'return_url' => $context->token()?->getTargetUrl(),
                'metadata' => [
                    'payum_reference' => $context->merchantReference(),
                ],
            ];

            $money = $subscription?->getMoney();
            $interval = $subscription?->getInterval();

            if (null !== $subscription?->getPlanReference()) {
                $request['plan'] = $subscription->getPlanReference();
            } elseif (null !== $money && null !== $interval) {
                $request['amount'] = (int) $money->getAmount();
                $request['currency'] = $money->getCurrency()->getCode();
                $request['interval'] = $interval->unit->value;
                $request['interval_count'] = $interval->count;
            } else {
                return SubscriptionResult::failed(new Failure(FailureReason::Configuration, message: 'Give the subscription a plan reference, or an amount and an interval.'));
            }

            $created = $this->api->createSubscription($request);
            $state['subscription_id'] = $created['id'];

            if (null !== $created['approve_url']) {
                return SubscriptionResult::pending(new Redirect($created['approve_url']));
            }
        }

        $found = $this->api->findSubscription($state['subscription_id']);

        $result = SubscriptionResult::active(new SubscriptionDetails(
            status: SubscriptionStatus::Active,
            reference: $found['id'],
            currentPeriodEnd: new DateTimeImmutable('@' . $found['current_period_end']),
        ));

        $invoice = $found['latest_invoice'];

        if (null === $invoice) {
            return $result;
        }

        return $result->withRenewal(new Renewal(
            $invoice['id'],
            new Money($invoice['amount'], new Currency($invoice['currency'])),
            PaymentStatus::Captured,
        ));
    }
}

final class DocsCancelSubscriptionHandler implements CancelSubscriptionHandlerInterface
{
    public function __construct(
        private readonly DocsSubscriptionApi $api,
    ) {
    }

    public function handle(CancelSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $subscription = $command->subscription();

        $found = $this->api->cancelSubscription((string) $subscription->getReference(), atPeriodEnd: $command->atPeriodEnd);

        return SubscriptionResult::updated(new SubscriptionDetails(
            status: $command->atPeriodEnd ? null : SubscriptionStatus::Canceled,
            cancelAt: $command->atPeriodEnd ? $subscription->getCurrentPeriodEnd() : null,
            canceledAt: $command->atPeriodEnd ? null : new DateTimeImmutable('@' . $found['canceled_at']),
        ));
    }
}

final class DocsSyncSubscriptionHandler implements SyncSubscriptionHandlerInterface
{
    public function __construct(
        private readonly DocsSubscriptionApi $api,
    ) {
    }

    public function handle(SyncSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $found = $this->api->findSubscription((string) $command->subscription()->getReference());

        return SubscriptionResult::updated(new SubscriptionDetails(
            status: SubscriptionStatus::tryFrom($found['status']) ?? SubscriptionStatus::Unknown,
            currentPeriodEnd: new DateTimeImmutable('@' . $found['current_period_end']),
        ));
    }
}

final class DocsSubscriptionNotifyHandler implements NotifyHandlerInterface
{
    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        $event = DocsSubscriptionApi::$webhook;

        return WebhookEvent::verified(
            $event,
            $event['id'],
            $event['type'],
            reference: $event['data']['subscription'],
            merchantReference: $event['data']['metadata']['payum_reference'] ?? null,
        );
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if (null === $context->subscription() || ! in_array($event->type, ['invoice.paid', 'invoice.payment_failed'], true)) {
            return NotifyResult::ignored();
        }

        $invoice = $event->payload['data'];
        $paid = 'invoice.paid' === $event->type;

        return NotifyResult::handled()
            ->withSubscription(new SubscriptionDetails(
                status: $paid ? SubscriptionStatus::Active : SubscriptionStatus::PastDue,
                currentPeriodEnd: new DateTimeImmutable('@' . $invoice['period_end']),
            ))
            ->withRenewal(new Renewal(
                $invoice['id'],
                new Money($invoice['amount'], new Currency($invoice['currency'])),
                $paid ? PaymentStatus::Captured : PaymentStatus::Failed,
                failure: $paid ? null : new Failure(FailureReason::Declined, $invoice['decline_code']),
            ));
    }
}
