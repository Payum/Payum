<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Event\WebhookReceived;
use Payum\Core\Event\WebhookRejected;
use Payum\Core\Exception\LogicException;
use Payum\Core\Legacy\Handler\NotifyActionHandler;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * End to end: a PSP posts to the notify endpoint, the gateway checks the message is genuine, and what
 * it says is recorded against the payment.
 */
final class NotifyTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        AcmeNotifyHandler::$signature = 'good-signature';
        VerificationRecordingMiddleware::$seen = null;
        AcmeNotifyHandler::$verifications = 0;
    }

    public function testShouldHandleAVerifiedEvent(): void
    {
        $payment = $this->buildPayment();
        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertInstanceOf(NotifyResult::class, $result);
        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame('txn_1', $result->transactionId);
    }

    public function testShouldRecordWhatTheEventSaidAboutThePayment(): void
    {
        $payment = new TrackedNotifyPayment();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    public function testShouldHandleAnEventAboutAPayout(): void
    {
        $payout = new Payout();
        $payout->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayout($payout));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(PaymentStatus::Captured, $payout->getStatus());
    }

    public function testShouldRefuseAMessageThatIsNotGenuine(): void
    {
        $payum = $this->buildPayum($this->buildRequest('forged'));

        $this->expectException(WebhookNotVerifiedException::class);

        $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment()));
    }

    public function testShouldLeaveTheStatusAloneWhenTheMessageIsNotGenuine(): void
    {
        $payment = new TrackedNotifyPayment();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('forged'));

        try {
            $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));
        } catch (WebhookNotVerifiedException) {
            // The point of the test is what did not happen.
        }

        $this->assertSame(PaymentStatus::Pending, $payment->getStatus());
    }

    public function testShouldLeaveThePaymentAloneForAnEventItIgnores(): void
    {
        $payment = new TrackedNotifyPayment();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('good-signature', 'invoice.drafted'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertNull($result->status);
        $this->assertSame(PaymentStatus::Pending, $payment->getStatus());
    }

    public function testShouldReadTheStateAPreviousCommandWrote(): void
    {
        $payment = $this->buildPayment();
        $payment->setDetails([
            'checkout_id' => 'chk_1',
        ]);

        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertSame('chk_1', $result->raw['seen_checkout']);
    }

    public function testShouldLetAGatewayThatCannotVerifyReReadFromThePsp(): void
    {
        $payment = $this->buildPayment();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('irrelevant'))
            ->registerGateway('trusting', new TrustingNotifyConfig())
            ->getPayum();

        $result = $payum->getGateway('trusting')->execute(NotifyCommand::forPayment($payment));

        // The event was taken on trust, so the answer comes from asking the PSP rather than from the
        // message.
        $this->assertSame(PaymentStatus::Refunded, $result->status);
    }

    public function testShouldRunVerificationInsideThePipeline(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('forged'))
            ->addMiddleware(new VerificationRecordingMiddleware())
            ->registerGateway('acme', new AcmeNotifyConfig())
            ->getPayum();

        try {
            $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment()));
        } catch (WebhookNotVerifiedException) {
            // The point of the test is what the middleware saw before this was rethrown.
        }

        $this->assertInstanceOf(WebhookNotVerifiedException::class, VerificationRecordingMiddleware::$seen);
    }

    public function testShouldAnswerThePspWithWhatTheHandlerAskedFor(): void
    {
        $payum = $this->buildPayum($this->buildRequest('good-signature'));
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify([
            'payum_token' => $token,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[accepted]', $response->getContent());
    }

    public function testShouldAnswerNoContentWhenTheHandlerNamesNothing(): void
    {
        $payum = $this->buildPayum($this->buildRequest('good-signature', 'invoice.drafted'));
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify([
            'payum_token' => $token,
        ]);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testShouldAnswerBadRequestWithoutSayingWhyWhenVerificationFails(): void
    {
        $payum = $this->buildPayum($this->buildRequest('forged'));
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify([
            'payum_token' => $token,
        ]);

        // Whoever failed the check is misconfigured or probing. Neither is helped by the detail.
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testShouldHandleAPreVerifiedEventWithoutVerifyingAgain(): void
    {
        $payum = $this->buildPayum($this->buildRequest('forged'));
        $event = $this->buildEvent();

        $command = NotifyCommand::forPayment($this->buildPayment())->withEvent($event);
        $result = $payum->getGateway('acme')->execute($command);

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(0, AcmeNotifyHandler::$verifications);
    }

    public function testShouldStillDispatchWebhookReceivedForAPreVerifiedEvent(): void
    {
        $seen = [];
        $events = new class($seen) implements EventDispatcherInterface {
            /**
             * @param list<object> $seen
             */
            public function __construct(private array &$seen)
            {
            }

            public function dispatch(object $event): object
            {
                $this->seen[] = $event;

                return $event;
            }
        };
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('forged'))
            ->addGlobalService(EventDispatcherInterface::class, $events)
            ->registerGateway('acme', new AcmeNotifyConfig())
            ->getPayum();
        $event = $this->buildEvent();

        $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment())->withEvent($event));

        $received = array_values(array_filter($seen, static fn (object $e): bool => $e instanceof WebhookReceived));
        $this->assertCount(1, $received);
        $this->assertSame($event, $received[0]->webhook);
    }

    public function testShouldNotReadTheHttpRequestForAPreVerifiedEvent(): void
    {
        $throwing = $this->createStub(ServerRequestInterface::class);
        foreach (['getHeaderLine', 'getHeaders', 'getBody', 'getMethod', 'getUri', 'getParsedBody'] as $method) {
            $throwing->method($method)->willThrowException(new \LogicException($method . ' was called.'));
        }
        $payum = $this->buildPayum($throwing);
        $event = $this->buildEvent();

        $command = NotifyCommand::forPayment($this->buildPayment())->withEvent($event);
        $result = $payum->getGateway('acme')->execute($command);

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testVerifyWebhookShouldReturnTheHandlersEvent(): void
    {
        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $event = $payum->getGateway('acme')->verifyWebhook($this->buildRequest('good-signature'));

        $this->assertTrue($event->isVerified());
        $this->assertSame('txn_1', $event->payload['transaction']);
    }

    public function testVerifyWebhookShouldDispatchWebhookRejectedAndRethrow(): void
    {
        $seen = [];
        $events = $this->recordingDispatcher($seen);
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('forged'))
            ->addGlobalService(EventDispatcherInterface::class, $events)
            ->registerGateway('acme', new AcmeNotifyConfig())
            ->getPayum();

        try {
            $payum->getGateway('acme')->verifyWebhook($this->buildRequest('forged'));
            $this->fail('Expected WebhookNotVerifiedException.');
        } catch (WebhookNotVerifiedException) {
        }

        $rejected = array_values(array_filter($seen, static fn (object $e): bool => $e instanceof WebhookRejected));
        $this->assertCount(1, $rejected);
        $this->assertSame('acme', $rejected[0]->gatewayName);
    }

    public function testVerifyWebhookShouldRefuseAGatewayWithoutWebhooks(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('mute', new MuteNotifyConfig())
            ->getPayum();

        $this->expectException(LogicException::class);

        $payum->getGateway('mute')->verifyWebhook($this->buildRequest('good-signature'));
    }

    public function testVerifyWebhookShouldRefuseALegacyNotifyAction(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('old', new LegacyNotifyConfig())
            ->getPayum();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('1.x');

        $payum->getGateway('old')->verifyWebhook($this->buildRequest('good-signature'));
    }

    public function testNotifyShouldAnswerWithTheEventsAcknowledgementWhenTheHandlerNamesNone(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('good-signature'))
            ->registerGateway('acme', new AckFromEventConfig())
            ->getPayum();
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify(['payum_token' => $token]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[accepted]', $response->getContent());
    }

    public function testNotifyShouldPreferTheHandlersAcknowledgement(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('good-signature'))
            ->registerGateway('acme', new AckBothConfig())
            ->getPayum();
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify(['payum_token' => $token]);

        $this->assertSame('[from-handler]', $response->getContent());
    }

    /**
     * @param list<object> $seen
     */
    private function recordingDispatcher(array &$seen): EventDispatcherInterface
    {
        return new class($seen) implements EventDispatcherInterface {
            /**
             * @param list<object> $seen
             */
            public function __construct(private array &$seen)
            {
            }

            public function dispatch(object $event): object
            {
                $this->seen[] = $event;

                return $event;
            }
        };
    }

    /**
     * @param Payum<object> $payum
     */
    private function mintNotifyToken(Payum $payum): TokenInterface
    {
        $payment = $this->buildPayment();
        $payum->getStorage($payment::class)->update($payment);

        return $payum->getTokenFactory()->createNotifyToken('acme', $payment);
    }

    private function buildEvent(): WebhookEvent
    {
        return WebhookEvent::verified(['transaction' => 'txn_1'], 'evt_1', 'payment.captured');
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDescription('An order');

        return $payment;
    }

    private function buildRequest(string $signature, string $type = 'payment.captured'): ServerRequestInterface
    {
        $body = json_encode([
            'id' => 'evt_1',
            'type' => $type,
            'transaction' => 'txn_1',
        ], JSON_THROW_ON_ERROR);

        return Psr17FactoryDiscovery::findServerRequestFactory()
            ->createServerRequest('POST', 'https://payum.dev/notify.php')
            ->withHeader('Acme-Signature', $signature)
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream($body));
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(ServerRequestInterface $request): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            // What a framework does: hand Payum the real request instead of the one built from globals.
            ->addGlobalService(ServerRequestInterface::class, $request)
            ->registerGateway('acme', new AcmeNotifyConfig())
            ->getPayum();
    }
}

final class AcmeNotifyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeNotifyGateway::class;
    }
}

final class AcmeNotifyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeNotifyConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeNotifyHandler::class];
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

final class AcmeNotifyHandler implements NotifyHandlerInterface
{
    public static string $signature = 'good-signature';

    public static int $verifications = 0;

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ('payment.captured' !== $event->type) {
            return NotifyResult::ignored();
        }

        return NotifyResult::handled(
            PaymentStatus::Captured,
            Acknowledgement::ok('[accepted]'),
            $event->payload['transaction'],
            raw: [
                'seen_checkout' => $context->state()['checkout_id'],
            ],
        );
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        ++self::$verifications;

        if (! hash_equals(self::$signature, $request->getHeaderLine('Acme-Signature'))) {
            throw new WebhookNotVerifiedException('The signature does not match.');
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return WebhookEvent::verified($payload, $payload['id'], $payload['type']);
    }
}

final class TrustingNotifyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return TrustingNotifyGateway::class;
    }
}

final class TrustingNotifyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return TrustingNotifyConfig::class;
    }

    public function handlers(): array
    {
        return [TrustingNotifyHandler::class, TrustingSyncHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://trusting.test/logo.svg');
    }

    public function name(): string
    {
        return 'Trusting';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://trusting.test');
    }
}

final class TrustingNotifyHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ($event->isVerified()) {
            return NotifyResult::handled(PaymentStatus::Captured);
        }

        $payment = $context->payment();

        if (! $payment instanceof PaymentInterface) {
            return NotifyResult::ignored();
        }

        $synced = $context->execute(SyncCommand::forPayment($payment));

        return NotifyResult::handled($synced->status);
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::unverified([]);
    }
}

final class TrustingSyncHandler implements SyncHandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        return SyncResult::synced(PaymentStatus::Refunded, 'txn_2');
    }
}

final class VerificationRecordingMiddleware implements MiddlewareInterface
{
    public static ?WebhookNotVerifiedException $seen = null;

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        try {
            return $next($command, $context);
        } catch (WebhookNotVerifiedException $e) {
            self::$seen = $e;

            throw $e;
        }
    }
}

class TrackedNotifyPayment extends Payment
{
}

final class MuteNotifyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return MuteNotifyGateway::class;
    }
}

final class MuteNotifyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return MuteNotifyConfig::class;
    }

    public function handlers(): array
    {
        return [];
    }

    public function logo(): Logo
    {
        return Url::create('https://mute.test/logo.svg');
    }

    public function name(): string
    {
        return 'Mute';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://mute.test');
    }
}

final class LegacyNotifyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return LegacyNotifyGateway::class;
    }
}

final class LegacyNotifyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return LegacyNotifyConfig::class;
    }

    public function handlers(): array
    {
        return [NotifyActionHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://old.test/logo.svg');
    }

    public function name(): string
    {
        return 'Old';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://old.test');
    }
}

final class AckFromEventConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AckFromEventGateway::class;
    }
}

final class AckFromEventGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AckFromEventConfig::class;
    }

    public function handlers(): array
    {
        return [AckFromEventHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://ack.test/logo.svg');
    }

    public function name(): string
    {
        return 'Ack';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://ack.test');
    }
}

/**
 * Verification names the acknowledgement; handling names none.
 */
class AckFromEventHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::handled();
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::verified([], 'evt_1', 'payment.captured', Acknowledgement::ok('[accepted]'));
    }
}

final class AckBothConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AckBothGateway::class;
    }
}

final class AckBothGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AckBothConfig::class;
    }

    public function handlers(): array
    {
        return [AckBothHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://ack.test/logo.svg');
    }

    public function name(): string
    {
        return 'AckBoth';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://ack.test');
    }
}

final class AckBothHandler extends AckFromEventHandler
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::handled(null, Acknowledgement::ok('[from-handler]'));
    }
}
