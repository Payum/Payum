<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use ArrayAccess;
use League\Uri\Uri;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Legacy\Handler\CaptureActionHandler;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * An action nobody has ported yet, driven by the command that means the same thing.
 */
final class ActionToHandlerAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/pay',
        ];

        $_GET = [];
    }

    public function testShouldDriveTheActionFromACommandAndReportWhatItThrew(): void
    {
        $payment = $this->buildPayment();

        $result = $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $this->assertInstanceOf(CaptureResult::class, $result);
        $this->assertInstanceOf(Redirect::class, $result->next);
        $this->assertSame('https://psp.test/checkout', $result->next->url);
        $this->assertSame(PaymentStatus::Pending, $result->status);

        // What the action wrote into the details is on the payment, the same as it would be after the
        // 1.x request. That is what makes the second phase find it.
        $this->assertSame('chk_1', $payment->getDetails()['checkout_id']);
    }

    public function testShouldReportTheStatusTheGatewaysOwnStatusActionAnswersWith(): void
    {
        $payment = $this->buildPayment();
        $payment->setDetails([
            'checkout_id' => 'chk_1',
        ]);

        $result = $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertNull($result->next);
    }

    public function testShouldStillAnswerTheOneXRequestTheActionUsedToAnswer(): void
    {
        // The application has not moved, the gateway has: the request becomes a command, the command
        // reaches the adapter, and the reply the action threw comes back out as a reply.
        $this->expectException(HttpRedirect::class);

        $this->buildPayum()->getGateway('acme')->execute(new Capture($this->buildPayment()));
    }

    public function testShouldRethrowAReplyNoNextActionMeansTheSameAs(): void
    {
        $payment = $this->buildPayment();
        $payment->setDetails([
            'render' => true,
        ]);

        $this->expectException(HttpResponse::class);

        $this->buildPayum()->getGateway('acme')->execute(CaptureCommand::forPayment($payment));
    }

    public function testShouldKeepTheOneXMachineryForAGatewayWhoseOnlyLegacyIsAnAdapter(): void
    {
        // AloneCaptureAction dispatches GetHttpRequest, which only core's own action answers. A gateway
        // declaring no actions of its own still gets those, or an adapted action could not run.
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('alone', new AloneConfig())
            ->getPayum();

        $result = $payum->getGateway('alone')->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertInstanceOf(Redirect::class, $result->next);
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('EUR');

        return $payment;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AdaptedConfig())
            ->getPayum();
    }
}

final class AdaptedConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AdaptedGateway::class;
    }
}

final class AdaptedGateway implements PaymentGateway, DeclaresActions, ContainerConfiguration
{
    public function actions(): array
    {
        return [AdaptedStatusAction::class];
    }

    public function configClass(): string
    {
        return AdaptedConfig::class;
    }

    public function configureContainer(): array
    {
        return [
            CaptureActionHandler::class => static fn (): CaptureActionHandler => new CaptureActionHandler(new AdaptedCaptureAction()),
        ];
    }

    public function handlers(): array
    {
        return [CaptureActionHandler::class];
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

final class AloneConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AloneGateway::class;
    }
}

final class AloneGateway implements PaymentGateway, ContainerConfiguration
{
    public function configClass(): string
    {
        return AloneConfig::class;
    }

    public function configureContainer(): array
    {
        return [
            CaptureActionHandler::class => static fn (): CaptureActionHandler => new CaptureActionHandler(new AloneCaptureAction()),
        ];
    }

    public function handlers(): array
    {
        return [CaptureActionHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://alone.test/logo.svg');
    }

    public function name(): string
    {
        return 'Alone';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://alone.test');
    }
}

/**
 * Untouched 1.x code: reads the details, dispatches a sub-request, throws a reply.
 */
final class AdaptedCaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new GetHttpRequest());

        if ($details['render']) {
            throw new HttpResponse('<form></form>');
        }

        if (! $details['checkout_id']) {
            $details['checkout_id'] = 'chk_1';

            throw new HttpRedirect('https://psp.test/checkout');
        }

        $details['paid'] = true;
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }
}

final class AloneCaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        $this->gateway->execute(new GetHttpRequest());

        throw new HttpRedirect('https://psp.test/checkout');
    }

    public function supports($request): bool
    {
        return $request instanceof Capture;
    }
}

final class AdaptedStatusAction implements ActionInterface
{
    public function execute($request): void
    {
        /** @var GetStatusInterface $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details['paid'] ? $request->markCaptured() : $request->markPending();
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface && $request->getModel() instanceof ArrayAccess;
    }
}
