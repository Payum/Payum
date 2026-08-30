<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Event\CommandDispatched;
use Payum\Core\Event\CommandFailed;
use Payum\Core\Event\Event;
use Payum\Core\Event\FailureRaised;
use Payum\Core\Event\HandlerResolved;
use Payum\Core\Event\ResultReturned;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Event\WebhookReceived;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Event\RecordingEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * End to end: an application registers a PSR-14 dispatcher and hears about what happens to a payment.
 */
final class EventDispatchTest extends TestCase
{
    private RecordingEventDispatcher $events;

    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        $this->events = new RecordingEventDispatcher();

        EventfulCaptureHandler::$answer = static fn (): CaptureResult => CaptureResult::captured('txn_1');
    }

    public function testShouldDispatchNothingWhenTheApplicationRegisteredNoDispatcher(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new EventfulConfig())
            ->getPayum();

        $result = $payum->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

        // Nothing to assert but that a command runs unchanged against the no-op dispatcher.
        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldAnnounceTheWholeLifecycleOfASuccessfulCommand(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildTrackedPayment()));

        $this->assertSame([
            CommandDispatched::class,
            HandlerResolved::class,
            StatusChanged::class,
            ResultReturned::class,
        ], $this->events->classes());
    }

    public function testShouldCarryTheCommandAndTheContextOnEveryEvent(): void
    {
        $payment = $this->buildPayment();
        $command = CaptureCommand::forPayment($payment);

        $this->buildPayum()->getGateway('acme')->execute($command);

        foreach ($this->events->ofType(Event::class) as $event) {
            $this->assertSame($command, $event->command);
            $this->assertSame($payment, $event->context->subject());
            $this->assertSame('acme', $event->context->gatewayName());
        }
    }

    public function testShouldNameTheHandlerThatAnsweredTheCommand(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $resolved = $this->events->ofType(HandlerResolved::class);

        $this->assertCount(1, $resolved);
        $this->assertInstanceOf(EventfulCaptureHandler::class, $resolved[0]->handler);
    }

    public function testShouldCarryTheResultTheHandlerReturned(): void
    {
        $result = $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $returned = $this->events->ofType(ResultReturned::class);

        $this->assertCount(1, $returned);
        $this->assertSame($result, $returned[0]->result);
    }

    public function testShouldReportBothEndsOfAStatusChange(): void
    {
        $payment = $this->buildTrackedPayment(PaymentStatus::Pending);

        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $changed = $this->events->ofType(StatusChanged::class);

        $this->assertCount(1, $changed);
        $this->assertSame($payment, $changed[0]->subject);
        $this->assertSame(PaymentStatus::Pending, $changed[0]->from);
        $this->assertSame(PaymentStatus::Captured, $changed[0]->to);
    }

    public function testShouldSayNothingWhenTheStatusDidNotMove(): void
    {
        $payment = $this->buildTrackedPayment(PaymentStatus::Captured);

        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        // A redelivered webhook must not fulfil the order a second time.
        $this->assertSame([], $this->events->ofType(StatusChanged::class));
    }

    public function testShouldSayNothingAboutTheStatusOfAPaymentThatDoesNotTrackOne(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertSame([], $this->events->ofType(StatusChanged::class));
    }

    public function testShouldRaiseAFailureAfterTheResultThatCarriesIt(): void
    {
        $failure = new Failure(FailureReason::InsufficientFunds, 'card_declined');

        EventfulCaptureHandler::$answer = static fn (): CaptureResult => CaptureResult::failed($failure);

        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertSame([
            CommandDispatched::class,
            HandlerResolved::class,
            ResultReturned::class,
            FailureRaised::class,
        ], $this->events->classes());

        $raised = $this->events->ofType(FailureRaised::class);

        $this->assertSame($failure, $raised[0]->failure);
        $this->assertSame($failure, $raised[0]->result->failure);
    }

    public function testShouldRaiseNoFailureForAResultThatSucceeded(): void
    {
        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertSame([], $this->events->ofType(FailureRaised::class));
    }

    public function testShouldReportAFaultAndStillLetItThrough(): void
    {
        $fault = new RuntimeException('psp exploded');

        EventfulCaptureHandler::$answer = static fn () => throw $fault;

        try {
            $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));

            $this->fail('Expected the exception to reach the caller.');
        } catch (RuntimeException $caught) {
            $this->assertSame($fault, $caught);
        }

        $failed = $this->events->ofType(CommandFailed::class);

        $this->assertCount(1, $failed);
        $this->assertSame($fault, $failed[0]->exception);
        $this->assertSame([], $this->events->ofType(ResultReturned::class));
    }

    public function testShouldAnnounceAWebhookOnceItHasBeenVerified(): void
    {
        $payum = $this->buildPayum($this->buildNotifyRequest('good-signature'));

        $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment()));

        $received = $this->events->ofType(WebhookReceived::class);

        $this->assertCount(1, $received);
        $this->assertTrue($received[0]->webhook->isVerified());
        $this->assertSame('evt_1', $received[0]->webhook->id);
    }

    public function testShouldAnnounceNoWebhookForAMessageThatIsNotGenuine(): void
    {
        $payum = $this->buildPayum($this->buildNotifyRequest('forged'));

        try {
            $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment()));
        } catch (WebhookNotVerifiedException) {
            // The point of the test is what a listener was not told.
        }

        $this->assertSame([], $this->events->ofType(WebhookReceived::class));
        $this->assertCount(1, $this->events->ofType(CommandFailed::class));
    }

    public function testShouldShareOneDispatcherBetweenEveryGateway(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(EventDispatcherInterface::class, $this->events)
            ->registerGateway('acme', new EventfulConfig())
            ->registerGateway('other', new EventfulConfig())
            ->getPayum();

        $payum->getGateway('acme')->execute(CaptureCommand::forPayment($this->buildPayment()));
        $payum->getGateway('other')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertCount(2, $this->events->ofType(CommandDispatched::class));
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);

        return $payment;
    }

    private function buildTrackedPayment(PaymentStatus $status = PaymentStatus::New): EventfulPayment
    {
        $payment = new EventfulPayment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setStatus($status);

        return $payment;
    }

    private function buildNotifyRequest(string $signature): ServerRequestInterface
    {
        $body = json_encode([
            'id' => 'evt_1',
        ], JSON_THROW_ON_ERROR);

        return Psr17FactoryDiscovery::findServerRequestFactory()
            ->createServerRequest('POST', 'https://payum.dev/notify.php')
            ->withHeader('Acme-Signature', $signature)
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream($body));
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(?ServerRequestInterface $request = null): Payum
    {
        $builder = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(EventDispatcherInterface::class, $this->events)
            ->registerGateway('acme', new EventfulConfig());

        if ($request instanceof ServerRequestInterface) {
            $builder->addGlobalService(ServerRequestInterface::class, $request);
        }

        return $builder->getPayum();
    }
}

final class EventfulConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return EventfulGateway::class;
    }
}

final class EventfulGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return EventfulConfig::class;
    }

    public function handlers(): array
    {
        return [EventfulCaptureHandler::class, EventfulNotifyHandler::class];
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

final class EventfulCaptureHandler implements CaptureHandlerInterface
{
    /**
     * @var callable(): CaptureResult
     */
    public static $answer;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return (self::$answer)();
    }
}

final class EventfulNotifyHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::handled(PaymentStatus::Captured, Acknowledgement::ok('[accepted]'));
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        if (! hash_equals('good-signature', $request->getHeaderLine('Acme-Signature'))) {
            throw new WebhookNotVerifiedException('The signature does not match.');
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return WebhookEvent::verified($payload, $payload['id']);
    }
}

class EventfulPayment extends Payment implements StatusAwareInterface
{
    private PaymentStatus $status = PaymentStatus::New;

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
