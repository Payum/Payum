<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\PayoutCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Exception\CommandNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Legacy\LegacyGatewayAdapter;
use Payum\Core\Legacy\LegacyReply;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout as PayoutRequest;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\RefundResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * A gateway package moves to handlers on its own schedule. An application that has moved to commands
 * should not lose access to the ones that have not.
 */
final class LegacyGatewayAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
            'REQUEST_URI' => '/capture.php',
        ];

        AcmeCaptureAction::$reply = null;
        AcmeCaptureAction::$seenHttpRequestUri = null;
        AcmeNotifyAction::$reply = null;
    }

    public function testShouldRunTheOneXActionAndReportWhatItsStatusActionSays(): void
    {
        $gateway = $this->wrap();
        $payment = $this->buildPayment();

        $result = $gateway->execute(CaptureCommand::forPayment($payment));

        $this->assertInstanceOf(CaptureResult::class, $result);
        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertNull($result->next);
        $this->assertTrue($result->isSuccessful());
    }

    public function testShouldTurnAThrownRedirectIntoTheNextActionThatMeansTheSameThing(): void
    {
        AcmeCaptureAction::$reply = new HttpRedirect('https://acme.test/checkout/1');

        $result = $this->wrap()->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(Redirect::class, $result->next);
        $this->assertSame('https://acme.test/checkout/1', $result->next->url);
        $this->assertSame(302, $result->next->statusCode);
        $this->assertTrue($result->requiresInteraction());
    }

    public function testShouldTurnAThrownPostRedirectIntoAPostRedirect(): void
    {
        AcmeCaptureAction::$reply = new HttpPostRedirect('https://acme.test/pay', [
            'amount' => '123',
        ]);

        $result = $this->wrap()->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(PostRedirect::class, $result->next);
        $this->assertSame('https://acme.test/pay', $result->next->url);
        $this->assertSame([
            'amount' => '123',
        ], $result->next->fields);
    }

    public function testShouldHandOverAReplyThatHasNoTwoPointOhEquivalent(): void
    {
        // 2.0 names a template and its context; there is no way back from markup a 1.x action already
        // rendered, so the caller gets the reply itself.
        AcmeCaptureAction::$reply = new HttpResponse('<form></form>', 200);

        $result = $this->wrap()->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(LegacyReply::class, $result->next);
        $this->assertInstanceOf(HttpResponse::class, $result->next->reply);
        $this->assertSame('<form></form>', $result->next->reply->getContent());
    }

    public function testShouldReEnterTheSameCommandAtTheSameUrl(): void
    {
        $payum = $this->buildPayum();
        $gateway = LegacyGatewayAdapter::wrap($payum->getGateway('acme'));

        $payment = $this->storeAPayment($payum);
        $token = $payum->getTokenFactory()->createCaptureToken('acme', $payment, 'done.php');

        AcmeCaptureAction::$reply = new HttpRedirect('https://acme.test/checkout/1');

        $first = $gateway->execute(CaptureCommand::forToken($token));

        $this->assertInstanceOf(Redirect::class, $first->next);
        $this->assertSame(PaymentStatus::Pending, $first->status);

        // The PSP returns the customer to the token's own url, and the very same command runs again. The
        // action recognises the psp token it left in the details and finishes.
        AcmeCaptureAction::$reply = null;

        $second = $gateway->execute(CaptureCommand::forToken($token));

        $this->assertNull($second->next);
        $this->assertSame(PaymentStatus::Captured, $second->status);
    }

    public function testShouldResolveASubRequestAOneXActionDispatches(): void
    {
        $this->wrap()->execute(CaptureCommand::forPayment($this->buildPayment()));

        // GetHttpRequest is answered by core's own action, which the 1.x gateway still carries.
        $this->assertSame('/capture.php', AcmeCaptureAction::$seenHttpRequestUri);
    }

    public function testShouldTranslateEachCommandIntoTheRequestThatMeansTheSameThing(): void
    {
        $gateway = $this->wrap();

        $captured = $this->buildPayment();
        $gateway->execute(CaptureCommand::forPayment($captured));

        $this->assertInstanceOf(RefundResult::class, $refunded = $gateway->execute(RefundCommand::forPayment($captured)));
        $this->assertSame(PaymentStatus::Refunded, $refunded->status);

        $this->assertSame(PaymentStatus::Authorized, $gateway->execute(AuthorizeCommand::forPayment($this->buildPayment()))->status);
        $this->assertSame(PaymentStatus::Canceled, $gateway->execute(CancelCommand::forPayment($this->buildPayment()))->status);
        $this->assertSame(PaymentStatus::PaidOut, $gateway->execute(PayoutCommand::forPayout($this->buildPayout()))->status);
    }

    public function testShouldReportWhatASyncReadBack(): void
    {
        $gateway = $this->wrap();
        $payment = $this->buildPayment();

        $gateway->execute(AuthorizeCommand::forPayment($payment));

        // The sync action is what moves it on, the way a PSP would have.
        $this->assertSame(PaymentStatus::Captured, $gateway->execute(SyncCommand::forPayment($payment))->status);
    }

    public function testShouldAnswerThePspFromTheReplyANotifyActionThrew(): void
    {
        AcmeNotifyAction::$reply = new HttpResponse('[accepted]', 200, [
            'X-Acme' => '1',
        ]);

        $result = $this->wrap()->execute(NotifyCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(NotifyResult::class, $result);
        $this->assertSame('[accepted]', $result->acknowledgement->body);
        $this->assertSame(200, $result->acknowledgement->status);
        $this->assertSame([
            'X-Acme' => '1',
        ], $result->acknowledgement->headers);
    }

    public function testShouldNameNoAcknowledgementWhenTheNotifyActionThrewNothing(): void
    {
        $result = $this->wrap()->execute(NotifyCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(NotifyResult::class, $result);

        // A 1.x notify that threw nothing is a 204, which is what a null acknowledgement means.
        $this->assertNull($result->acknowledgement);
    }

    public function testShouldInferCapabilitiesFromTheRegisteredActions(): void
    {
        $capabilities = $this->wrap()->capabilities();

        $this->assertContains(Capability::Capture, $capabilities);
        $this->assertContains(Capability::Refund, $capabilities);
        $this->assertContains(Capability::Webhooks, $capabilities);

        // Nothing on this gateway claims a Cancel carrying a details array.
        $this->assertNotContains(Capability::Cancel, $this->wrap(cancel: false)->capabilities());
    }

    public function testShouldNotReportACapabilityJustBecauseCoreShipsAnActionForIt(): void
    {
        // Core registers CapturePaymentAction on every gateway. Reading that as "this gateway can
        // capture" would report every gateway as capable of everything.
        $gateway = LegacyGatewayAdapter::wrap($this->buildPayum(capture: false)->getGateway('acme'));

        $this->assertNotContains(Capability::Capture, $gateway->capabilities());
    }

    public function testShouldReportACommandNoActionClaimsAsUnsupported(): void
    {
        $gateway = $this->wrap(cancel: false);

        $this->expectException(CommandNotSupportedException::class);

        $gateway->execute(CancelCommand::forPayment($this->buildPayment()));
    }

    public function testShouldLeaveAnUnansweredSubRequestAlone(): void
    {
        // The capture itself is claimed; what goes unanswered is the request the action dispatches from
        // inside it, and calling that "the gateway cannot capture" would send the reader to the wrong place.
        $gateway = LegacyGatewayAdapter::wrap($this->buildPayum(convertPayment: false)->getGateway('acme'));

        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessageMatches('/Convert/');

        $gateway->execute(CaptureCommand::forPayment($this->buildPayment()));
    }

    public function testShouldRefuseToDropWhatAOneXRequestCannotCarry(): void
    {
        $gateway = $this->wrap();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('an amount');

        // Silently refunding everything instead of half is the failure this prevents.
        $gateway->execute(RefundCommand::forPayment($this->buildPayment(), 500));
    }

    public function testShouldNameEverythingACommandCarriesThatWouldBeLost(): void
    {
        $gateway = $this->wrap();

        $this->expectExceptionMessage('an amount and a reason');

        $gateway->execute(RefundCommand::forPayment($this->buildPayment(), 500, 'duplicate'));
    }

    public function testShouldSayNothingAboutTheStatusWhenTheGatewayHasNoStatusAction(): void
    {
        $gateway = LegacyGatewayAdapter::wrap($this->buildPayum(status: false)->getGateway('acme'));

        // Nobody knows, which is not the same as new. Driven through Sync because core's own
        // CapturePaymentAction asks for a status before it converts, so a capture needs one to run at all.
        $this->assertNull($gateway->execute(SyncCommand::forPayment($this->buildPayment()))->status);
    }

    public function testShouldPassAOneXRequestStraightThrough(): void
    {
        $gateway = $this->wrap();
        $payment = $this->buildPayment();

        $gateway->execute(new Capture($payment));

        $this->assertSame('captured', ArrayObject::ensureArrayObject($payment->getDetails())['status']);
    }

    public function testShouldStillThrowTheReplyAOneXCallerIsWaitingToCatch(): void
    {
        AcmeCaptureAction::$reply = new HttpRedirect('https://acme.test/checkout/1');

        $this->expectException(HttpRedirect::class);

        $this->wrap()->execute(new Capture($this->buildPayment()));
    }

    public function testShouldStillReturnTheReplyWhenTheOneXCallerAsksToCatchIt(): void
    {
        AcmeCaptureAction::$reply = new HttpRedirect('https://acme.test/checkout/1');

        $reply = $this->wrap()->execute(new Capture($this->buildPayment()), true);

        $this->assertInstanceOf(HttpRedirect::class, $reply);
    }

    public function testShouldSaySoWhenThereAreNoActionsToRead(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('can only wrap a ' . Gateway::class);

        LegacyGatewayAdapter::wrap($this->createMock(GatewayInterface::class));
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('EUR');

        return $payment;
    }

    private function buildPayout(): Payout
    {
        $payout = new Payout();
        $payout->setTotalAmount(123);
        $payout->setCurrencyCode('EUR');

        return $payout;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(bool $capture = true, bool $cancel = true, bool $status = true, bool $convertPayment = true): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
            ->addGatewayFactory(
                'acme',
                static fn (array $config, GatewayFactoryInterface $core): AcmeGatewayFactory => new AcmeGatewayFactory([
                    'acme.capture' => $capture,
                    'acme.cancel' => $cancel,
                    'acme.status' => $status,
                    'acme.convert_payment' => $convertPayment,
                ], $core)
            )
            ->addGateway('acme', [
                'factory' => 'acme',
            ])
            ->getPayum()
        ;
    }

    /**
     * @param Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>> $payum
     */
    private function storeAPayment(Payum $payum): Payment
    {
        /** @var Payment $payment */
        $payment = $payum->getStorage(Payment::class)->create();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('EUR');
        $payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }

    private function wrap(bool $capture = true, bool $cancel = true, bool $status = true): LegacyGatewayAdapter
    {
        return LegacyGatewayAdapter::wrap($this->buildPayum($capture, $cancel, $status)->getGateway('acme'));
    }
}

/**
 * A 1.x gateway as every shipped one is built: a factory populating a flat config array with actions.
 */
final class AcmeGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'acme',
            'payum.factory_title' => 'Acme',
            'payum.action.authorize' => new AcmeAuthorizeAction(),
            'payum.action.refund' => new AcmeRefundAction(),
            'payum.action.payout' => new AcmePayoutAction(),
            'payum.action.sync' => new AcmeSyncAction(),
            'payum.action.notify' => new AcmeNotifyAction(),
            'payum.action.convert_payout' => new AcmeConvertPayoutAction(),
        ]);

        if (false !== $config['acme.capture']) {
            $config['payum.action.capture'] = new AcmeCaptureAction();
        }

        if (false !== $config['acme.cancel']) {
            $config['payum.action.cancel'] = new AcmeCancelAction();
        }

        if (false !== $config['acme.status']) {
            $config['payum.action.status'] = new AcmeStatusAction();
        }

        if (false !== $config['acme.convert_payment']) {
            $config['payum.action.convert_payment'] = new AcmeConvertPaymentAction();
        }
    }
}

final class AcmeCaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public static ?HttpResponse $reply = null;

    public static ?string $seenHttpRequestUri = null;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        self::$seenHttpRequestUri = $httpRequest->uri;

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['psp_token']) {
            $model['status'] = 'captured';

            return;
        }

        $model['psp_token'] = 'psp_1';
        $model['status'] = 'pending';

        if (self::$reply instanceof HttpResponse) {
            throw self::$reply;
        }

        $model['status'] = 'captured';
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeAuthorizeAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel())['status'] = 'authorized';
    }

    public function supports($request): bool
    {
        return $request instanceof Authorize && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeRefundAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel())['status'] = 'refunded';
    }

    public function supports($request): bool
    {
        return $request instanceof Refund && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeCancelAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel())['status'] = 'canceled';
    }

    public function supports($request): bool
    {
        return $request instanceof Cancel && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmePayoutAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel())['status'] = 'payedout';
    }

    public function supports($request): bool
    {
        return $request instanceof PayoutRequest && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeSyncAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel())['status'] = 'captured';
    }

    public function supports($request): bool
    {
        return $request instanceof Sync && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeNotifyAction implements ActionInterface
{
    public static ?HttpResponse $reply = null;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        ArrayObject::ensureArrayObject($request->getModel())['status'] = 'captured';

        if (self::$reply instanceof HttpResponse) {
            throw self::$reply;
        }
    }

    public function supports($request): bool
    {
        return $request instanceof Notify && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeStatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetStatusInterface $request */
        $status = ArrayObject::ensureArrayObject($request->getModel())['status'];

        match ($status) {
            'authorized' => $request->markAuthorized(),
            'canceled' => $request->markCanceled(),
            'captured' => $request->markCaptured(),
            'payedout' => $request->markPayedout(),
            'pending' => $request->markPending(),
            'refunded' => $request->markRefunded(),
            null => $request->markNew(),
            default => $request->markUnknown(),
        };
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface && $request->getModel() instanceof ArrayAccess;
    }
}

final class AcmeConvertPaymentAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Convert $request */
        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['amount'] = $payment->getTotalAmount();
        $details['currency'] = $payment->getCurrencyCode();

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && 'array' === $request->getTo()
        ;
    }
}

final class AcmeConvertPayoutAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Convert $request */
        /** @var PayoutInterface $payout */
        $payout = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payout->getDetails());
        $details['amount'] = $payout->getTotalAmount();
        $details['currency'] = $payout->getCurrencyCode();

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PayoutInterface
            && 'array' === $request->getTo()
        ;
    }
}
