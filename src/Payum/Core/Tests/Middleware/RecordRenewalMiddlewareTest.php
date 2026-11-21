<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DI\Container;
use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use LogicException;
use Money\Money;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\RecordRenewalMiddleware;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Subscription\RenewalPaymentFactoryInterface;
use Payum\Core\Tests\Mocks\Event\RecordingEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

final class RecordRenewalMiddlewareTest extends TestCase
{
    private string $dir;

    private RecordingEventDispatcher $events;

    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        // Payments are stored by a generated id here, so a second payment for one renewal shows up as a
        // second file.
        $this->dir = sys_get_temp_dir() . '/payum-renewal-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
        $this->events = new RecordingEventDispatcher();
        AcmeRenewalCreateHandler::$renewal = null;
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->dir . '/*') ?: []);
        rmdir($this->dir);
    }

    public function testShouldCreateAPaymentForARenewal(): void
    {
        $customer = new Customer();
        $customer->setCustomerId('cus_1');
        $customer->setEmail('jane@example.com');
        $subscription = new Subscription();
        $subscription->setCustomer($customer);
        $subscription->setDescription('Gold plan');
        AcmeRenewalCreateHandler::$renewal = new Renewal('in_1', Money::EUR(1500), PaymentStatus::Captured, isFirst: true);

        $payum = $this->payum();
        $payum->getStorage(Subscription::class)->update($subscription);
        $payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        $payments = $this->payments();
        $this->assertCount(1, $payments);
        $this->assertEquals(Money::EUR(1500), $payments[0]->getMoney());
        $this->assertSame('in_1', $payments[0]->getNumber());
        $this->assertSame('Gold plan', $payments[0]->getDescription());
        $this->assertSame('cus_1', $payments[0]->getClientId());
        $this->assertSame('jane@example.com', $payments[0]->getClientEmail());
        $this->assertSame(PaymentStatus::Captured, $payments[0]->getStatus());
        $this->assertInstanceOf(SubscriptionInterface::class, $payments[0]->getSubscription());
        $this->assertSame('sub_1', $payments[0]->getSubscription()->getReference());

        $events = $this->events->ofType(StatusChanged::class);
        $this->assertCount(1, $events);
        $this->assertSame(PaymentStatus::New, $events[0]->from);
        $this->assertSame(PaymentStatus::Captured, $events[0]->to);
    }

    public function testARedeliveredRenewalShouldNotCreateASecondPayment(): void
    {
        $payum = $this->subscribed();
        AcmeRenewalCreateHandler::$renewal = new Renewal('in_2', Money::EUR(1500), PaymentStatus::Captured);

        $payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));
        $payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));

        $this->assertCount(1, $this->payments());
        $this->assertCount(1, $this->events->ofType(StatusChanged::class));
    }

    public function testAPaidRetryShouldUpdateTheFailedRenewal(): void
    {
        $payum = $this->subscribed();

        AcmeRenewalCreateHandler::$renewal = new Renewal('in_1', Money::EUR(1500), PaymentStatus::Failed);
        $payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));
        AcmeRenewalCreateHandler::$renewal = new Renewal('in_1', Money::EUR(1500), PaymentStatus::Captured);
        $payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));

        $payments = $this->payments();
        $this->assertCount(1, $payments);
        $this->assertSame(PaymentStatus::Captured, $payments[0]->getStatus());

        $events = $this->events->ofType(StatusChanged::class);
        $this->assertCount(2, $events);
        $this->assertSame(PaymentStatus::Failed, $events[1]->from);
        $this->assertSame(PaymentStatus::Captured, $events[1]->to);
    }

    public function testTheApplicationCanSupplyTheFactory(): void
    {
        $factory = new class() implements RenewalPaymentFactoryInterface {
            public function create(SubscriptionInterface $subscription, Renewal $renewal): PaymentInterface
            {
                $payment = new Payment();
                $payment->setNumber('order-for-' . $renewal->transactionId);

                return $payment;
            }
        };
        $payum = $this->subscribed(static fn (PayumBuilder $builder): PayumBuilder => $builder->setRenewalPaymentFactory($factory));
        AcmeRenewalCreateHandler::$renewal = new Renewal('in_1', Money::EUR(1500), PaymentStatus::Captured);

        $payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));

        $payments = $this->payments();
        $this->assertCount(1, $payments);
        $this->assertSame('order-for-in_1', $payments[0]->getNumber());
        $this->assertSame(PaymentStatus::Captured, $payments[0]->getStatus());
    }

    public function testShouldBeInTheDefaultPipeline(): void
    {
        $resolved = CoreGatewayFactory::defaultMiddleware()->resolve(
            new Container((new CoreGatewayFactory())->createConfig([]))
        );

        $this->assertContains(RecordRenewalMiddleware::class, array_map(static fn (object $m): string => $m::class, $resolved));
    }

    /**
     * A gateway whose subscription sub_1 is already created, with no charge taken.
     *
     * @param (callable(PayumBuilder): PayumBuilder)|null $configure
     *
     * @return Payum<object>
     */
    private function subscribed(?callable $configure = null): Payum
    {
        $payum = $this->payum($configure);
        $subscription = new Subscription();
        $payum->getStorage(Subscription::class)->update($subscription);
        $payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));
        $this->events->dispatched = [];

        return $payum;
    }

    /**
     * @param (callable(PayumBuilder): PayumBuilder)|null $configure
     *
     * @return Payum<object>
     */
    private function payum(?callable $configure = null): Payum
    {
        $builder = (new PayumBuilder())
            ->addStorage(Payment::class, new FilesystemStorage($this->dir, Payment::class))
            ->addDefaultStorages()
            ->addGlobalService(EventDispatcherInterface::class, $this->events)
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://payum.dev/webhooks/acme'))
            ->registerGateway('acme', new AcmeRenewalConfig());

        return ($configure ? $configure($builder) : $builder)->getPayum();
    }

    /**
     * @return list<Payment>
     */
    private function payments(): array
    {
        return array_map(static fn (string $file): Payment => unserialize((string) file_get_contents($file)), glob($this->dir . '/*') ?: []);
    }
}

final class AcmeRenewalConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeRenewalGateway::class;
    }
}

final class AcmeRenewalGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeRenewalConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeRenewalCreateHandler::class, AcmeRenewalNotifyHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Renewals';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmeRenewalCreateHandler implements CreateSubscriptionHandlerInterface
{
    public static ?Renewal $renewal = null;

    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $result = SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1'));

        return self::$renewal instanceof Renewal ? $result->withRenewal(self::$renewal) : $result;
    }
}

final class AcmeRenewalNotifyHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::handled()->withRenewal(AcmeRenewalCreateHandler::$renewal ?? throw new LogicException('No renewal to report.'));
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::verified([], 'evt_1', 'invoice.paid', null, 'sub_1');
    }
}
