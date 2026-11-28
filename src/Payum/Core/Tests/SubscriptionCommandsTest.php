<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use DateTimeImmutable;
use League\Uri\Uri;
use Money\Money;
use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\SyncSubscriptionCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedOperationException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CancelSubscriptionHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\SyncSubscriptionHandlerInterface;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenPurpose;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class SubscriptionCommandsTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        AcmeSubscriptionsGateway::$capabilities = [];
        AcmeSubscriptionsGateway::$handlers = [AcmeCreateSubscriptionHandler::class, AcmeCancelSubscriptionHandler::class, AcmeSyncSubscriptionHandler::class];
        AcmeCreateSubscriptionHandler::$approved = false;
        AcmeCreateSubscriptionHandler::$keys = [];
        AcmeCancelSubscriptionHandler::$atPeriodEnd = null;
    }

    public function testShouldDeriveTheSubscriptionCapabilities(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertContains(Capability::CreateSubscription, $gateway->capabilities());
        $this->assertContains(Capability::CancelSubscription, $gateway->capabilities());
        $this->assertContains(Capability::SyncSubscription, $gateway->capabilities());
        $this->assertSame(Capability::CreateSubscription, CreateSubscriptionCommand::capability());
        $this->assertSame(Capability::CancelSubscription, CancelSubscriptionCommand::capability());
        $this->assertSame(Capability::SyncSubscription, SyncSubscriptionCommand::capability());
        $this->assertSame('cancel_at_period_end', Capability::CancelAtPeriodEnd->value);
    }

    public function testShouldCarryTheSubscriptionAsTheSubject(): void
    {
        $subscription = new Subscription();
        $token = $this->createMock(TokenInterface::class);

        $this->assertSame($subscription, CreateSubscriptionCommand::forSubscription($subscription, 'key-1')->subject());
        $this->assertSame('key-1', CreateSubscriptionCommand::forSubscription($subscription, 'key-1')->idempotencyKey);
        $this->assertSame($token, CreateSubscriptionCommand::forToken($token)->token());
        $this->assertNull(CreateSubscriptionCommand::forToken($token)->subject());
        $this->assertTrue(CancelSubscriptionCommand::forSubscription($subscription, true)->atPeriodEnd);
        $this->assertFalse(CancelSubscriptionCommand::forSubscription($subscription)->atPeriodEnd);
        $this->assertSame('key-2', CancelSubscriptionCommand::forSubscription($subscription, idempotencyKey: 'key-2')->idempotencyKey);
        $this->assertSame($subscription, SyncSubscriptionCommand::forSubscription($subscription)->subscription());
    }

    public function testShouldRefuseACreateWithNothingToCreate(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs either a token or a subscription');

        new CreateSubscriptionCommand();
    }

    public function testShouldPassTheIdempotencyKeyToTheHandler(): void
    {
        $payum = $this->buildPayum();
        AcmeCreateSubscriptionHandler::$approved = true;

        $payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($this->savedSubscription($payum), 'key-1'));

        $this->assertSame(['key-1'], AcmeCreateSubscriptionHandler::$keys);
    }

    public function testCancelAtPeriodEndNeedsTheCapability(): void
    {
        $payum = $this->buildPayum();
        $subscription = $this->savedSubscription($payum);
        AcmeSubscriptionsGateway::$capabilities = [Capability::Recurring];

        try {
            $payum->getGateway('acme')->execute(CancelSubscriptionCommand::forSubscription($subscription, true));
            $this->fail('A cancellation at period end went through on a gateway that cannot make one.');
        } catch (UnsupportedOperationException $e) {
            $this->assertStringContainsString('Gateway "acme" does not declare Capability::CancelAtPeriodEnd', $e->getMessage());
        }

        $this->assertNull(AcmeCancelSubscriptionHandler::$atPeriodEnd);

        $payum->getGateway('acme')->execute(CancelSubscriptionCommand::forSubscription($subscription));
        $this->assertFalse(AcmeCancelSubscriptionHandler::$atPeriodEnd);

        AcmeSubscriptionsGateway::$capabilities = [Capability::CancelAtPeriodEnd];
        $result = $payum->getGateway('acme')->execute(CancelSubscriptionCommand::forSubscription($subscription, true));
        $this->assertTrue(AcmeCancelSubscriptionHandler::$atPeriodEnd);
        $this->assertInstanceOf(SubscriptionResult::class, $result);
        $this->assertNull($result->status);
        $this->assertSame(SubscriptionStatus::Active, $result->subscription()?->status);
    }

    public function testApplicationScheduledSubscriptionsAreNotCreatedByTheGateway(): void
    {
        $payum = $this->buildPayum();
        $subscription = $this->savedSubscription($payum);
        $subscription->setSchedule(SubscriptionSchedule::Application);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('an application-scheduled subscription is charged with CaptureCommand');

        $payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));
    }

    public function testSubscribeShouldFollowARedirect(): void
    {
        $payum = $this->buildPayum();
        $token = $payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'acme', $this->savedSubscription($payum), 'subscribed.php');

        $response = $payum->subscribe(Request::create($token->getTargetUrl()));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://acme.test/approve', $response->getTargetUrl());
        $this->assertStringContainsString('subscribe.php', $token->getTargetUrl());
        $this->assertStringContainsString('subscribed.php', $token->getAfterUrl());
    }

    public function testSubscribeShouldGoToTheAfterUrlOnceActive(): void
    {
        $payum = $this->buildPayum();
        $token = $payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'acme', $this->savedSubscription($payum), 'subscribed.php');
        AcmeCreateSubscriptionHandler::$approved = true;

        $response = $payum->subscribe(Request::create($token->getTargetUrl()));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($token->getAfterUrl(), $response->getTargetUrl());
    }

    public function testSubscribeShouldRefuseAGatewayWithoutAHandler(): void
    {
        AcmeSubscriptionsGateway::$handlers = [AcmeSyncSubscriptionHandler::class];
        $payum = $this->buildPayum();
        $token = $payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'acme', $this->savedSubscription($payum), 'subscribed.php');

        $this->expectException(UnsupportedOperationException::class);
        $this->expectExceptionMessage('Gateway "acme" has no handler for');

        $payum->subscribe(Request::create($token->getTargetUrl()));
    }

    public function testSubscriptionResultsConcludeNothingAboutAPayment(): void
    {
        $details = new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1');
        $failure = new Failure(FailureReason::Declined);
        $renewal = new Renewal('in_1', Money::EUR(1000), PaymentStatus::Captured, isFirst: true);

        $active = SubscriptionResult::active($details, [
            'id' => 'sub_1',
        ])->withRenewal($renewal)->withStoredPaymentMethod(StoredPaymentMethodDetails::active('pm_1'));

        $this->assertNull($active->status);
        $this->assertSame($details, $active->subscription());
        $this->assertSame($renewal, $active->renewal());
        $this->assertSame('pm_1', $active->storedPaymentMethod()?->reference);
        $this->assertSame([
            'id' => 'sub_1',
        ], $active->raw);
        $this->assertNull(SubscriptionResult::active($details)->renewal());
        $this->assertTrue(SubscriptionResult::pending(new Redirect('https://acme.test'))->requiresInteraction());
        $this->assertNull(SubscriptionResult::pending(new Redirect('https://acme.test'))->subscription());
        $this->assertSame($details, SubscriptionResult::updated($details)->subscription());
        $this->assertSame($failure, SubscriptionResult::failed($failure)->failure);
        $this->assertTrue(SubscriptionResult::failed($failure)->isFailed());
        $this->assertNull(SubscriptionResult::failed($failure)->subscription());
    }

    public function testNotifyResultCarriesSubscriptionAndRenewal(): void
    {
        $details = new SubscriptionDetails(SubscriptionStatus::PastDue, currentPeriodEnd: new DateTimeImmutable('2026-12-01'));
        $renewal = new Renewal('in_2', Money::EUR(1000), PaymentStatus::Failed, failure: new Failure(FailureReason::InsufficientFunds));
        $handled = NotifyResult::handled();

        $result = $handled->withSubscription($details)->withRenewal($renewal);

        $this->assertSame($details, $result->subscription());
        $this->assertSame($renewal, $result->renewal());
        $this->assertFalse($result->renewal()->isFirst);
        $this->assertNull($result->renewal()->periodEnd);
        $this->assertNull($handled->subscription());
        $this->assertNull($handled->renewal());

        $acknowledgement = new Acknowledgement(409);
        $retry = NotifyResult::retry($acknowledgement)->withSubscription($details)->withRenewal($renewal);

        $this->assertTrue($retry->isRetry());
        $this->assertSame($acknowledgement, $retry->acknowledgement);
    }

    public function testCancelAtPeriodEndIsRefusedOnAGatewayThatDeclaresNothing(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('plain', new AcmeUndeclaredSubscriptionsConfig())
            ->getPayum();
        $subscription = $this->savedSubscription($payum);

        try {
            $payum->getGateway('plain')->execute(CancelSubscriptionCommand::forSubscription($subscription, true));
            $this->fail('A cancellation at period end went through on a gateway that declares no capabilities.');
        } catch (UnsupportedOperationException $e) {
            $this->assertStringContainsString('Gateway "plain" does not declare Capability::CancelAtPeriodEnd', $e->getMessage());
        }

        $this->assertNull(AcmeCancelSubscriptionHandler::$atPeriodEnd);

        $payum->getGateway('plain')->execute(CancelSubscriptionCommand::forSubscription($subscription));
        $this->assertFalse(AcmeCancelSubscriptionHandler::$atPeriodEnd);
    }

    public function testSubscribeShouldRefuseAnApplicationScheduledSubscriptionItLoadsFromTheToken(): void
    {
        $payum = $this->buildPayum();
        $subscription = $this->savedSubscription($payum);
        $subscription->setSchedule(SubscriptionSchedule::Application);
        $payum->getStorage(Subscription::class)->update($subscription);
        $token = $payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'acme', $subscription, 'subscribed.php');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('an application-scheduled subscription is charged with CaptureCommand');

        try {
            $payum->subscribe(Request::create($token->getTargetUrl()));
        } finally {
            $this->assertSame([], AcmeCreateSubscriptionHandler::$keys);
        }
    }

    /**
     * @param Payum<object> $payum
     */
    private function savedSubscription(Payum $payum): Subscription
    {
        $subscription = new Subscription();
        $subscription->setMoney(Money::EUR(1000));
        $payum->getStorage(Subscription::class)->update($subscription);

        return $subscription;
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmeSubscriptionsConfig())
            ->getPayum();
    }
}

final class AcmeSubscriptionsConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeSubscriptionsGateway::class;
    }
}

final class AcmeSubscriptionsGateway implements PaymentGateway, DeclaresCapabilities
{
    /**
     * @var list<Capability>
     */
    public static array $capabilities = [];

    /**
     * @var list<class-string<HandlerInterface>>
     */
    public static array $handlers = [];

    public function capabilities(): array
    {
        return self::$capabilities;
    }

    public function configClass(): string
    {
        return AcmeSubscriptionsConfig::class;
    }

    public function handlers(): array
    {
        return self::$handlers;
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

final class AcmeCreateSubscriptionHandler implements CreateSubscriptionHandlerInterface
{
    public static bool $approved = false;

    /**
     * @var list<string|null>
     */
    public static array $keys = [];

    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        self::$keys[] = $context->idempotencyKey();

        if (! self::$approved) {
            return SubscriptionResult::pending(new Redirect('https://acme.test/approve'));
        }

        return SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1'));
    }
}

final class AcmeCancelSubscriptionHandler implements CancelSubscriptionHandlerInterface
{
    public static ?bool $atPeriodEnd = null;

    public function handle(CancelSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        self::$atPeriodEnd = $command->atPeriodEnd;

        return SubscriptionResult::updated(new SubscriptionDetails(SubscriptionStatus::Active, cancelAt: new DateTimeImmutable('2026-12-01')));
    }
}

final class AcmeSyncSubscriptionHandler implements SyncSubscriptionHandlerInterface
{
    public function handle(SyncSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        return SubscriptionResult::updated(new SubscriptionDetails(SubscriptionStatus::Active));
    }
}

final class AcmeUndeclaredSubscriptionsConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeUndeclaredSubscriptionsGateway::class;
    }
}

final class AcmeUndeclaredSubscriptionsGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeUndeclaredSubscriptionsConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeCancelSubscriptionHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Undeclared Subscriptions';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}
