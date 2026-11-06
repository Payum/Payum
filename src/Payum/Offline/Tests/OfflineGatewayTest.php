<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Offline\Handler\CaptureHandler;
use Payum\Offline\OfflineConfig;
use Payum\Offline\OfflineGateway;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class OfflineGatewayTest extends TestCase
{
    public function testConfigAndGatewayNameEachOther(): void
    {
        $this->assertSame(OfflineConfig::class, (new OfflineGateway())->configClass());
        $this->assertSame(OfflineGateway::class, (new OfflineConfig())->getGatewayClass());
    }

    public function testHandlersImplyEveryOperationButWebhooks(): void
    {
        $this->assertEqualsCanonicalizing([
            Capability::Capture,
            Capability::Authorize,
            Capability::Refund,
            Capability::Cancel,
            Capability::Sync,
            Capability::Payout,
        ], HandlerMap::fromHandlers((new OfflineGateway())->handlers())->capabilities());
    }

    public function testShouldRegisterTheGatewayUnderSeveralNamesWithTheirOwnConfig(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('cash', new OfflineConfig())
            ->registerGateway('bank_transfer', new OfflineConfig(paid: false))
            ->getPayum();

        $cash = $payum->getGateway('cash')->execute(CaptureCommand::forPayment($this->payment()));
        $transfer = $payum->getGateway('bank_transfer')->execute(CaptureCommand::forPayment($this->payment()));

        $this->assertSame(PaymentStatus::Captured, $cash->status);
        $this->assertSame(PaymentStatus::Pending, $transfer->status);
    }

    public function testShouldRunAHandlerTheApplicationGaveInPlaceOfTheGatewaysOwn(): void
    {
        $ledger = new StoreCreditLedger(500);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('cash', new OfflineConfig())
            ->registerGateway('store_credit', new OfflineConfig(handlers: [new StoreCreditCaptureHandler($ledger)]))
            ->getPayum();

        $payment = $this->payment();
        $result = $payum->getGateway('store_credit')->execute(CaptureCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(377, $ledger->balance);
        $this->assertSame('captured', $payment->getDetails()['status'], 'The status is written to the details for a replaced handler too.');

        $payum->getGateway('cash')->execute(CaptureCommand::forPayment($this->payment()));
        $this->assertSame(377, $ledger->balance, 'Other registrations keep the gateway\'s own handler.');
    }

    public function testShouldAutowireAHandlerGivenByClassName(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(StoreCreditLedger::class, $ledger = new StoreCreditLedger(500))
            ->registerGateway('store_credit', new OfflineConfig(handlers: [StoreCreditCaptureHandler::class]))
            ->getPayum();

        $payum->getGateway('store_credit')->execute(CaptureCommand::forPayment($this->payment()));

        $this->assertSame(377, $ledger->balance);
    }

    public function testShouldRefuseAHandlerForACommandTheGatewayDoesNotTake(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(NotifyHandlerInterface::class);

        new OfflineConfig(handlers: [OfflineNotifyHandler::class]);
    }

    public function testShouldRefuseTwoHandlersForOneCommand(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Give one handler per command');

        new OfflineConfig(handlers: [CaptureHandler::class, StoreCreditCaptureHandler::class]);
    }

    public function testShouldAnswerA1xCaptureAndStatusRequest(): void
    {
        $gateway = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('offline', new OfflineConfig())
            ->getPayum()
            ->getGateway('offline');

        $payment = $this->payment();
        $gateway->execute(new Capture($payment));
        $gateway->execute($status = new GetHumanStatus($payment));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldReadTheStatusOfAPaymentCapturedBeforeTheUpgrade(): void
    {
        $gateway = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('offline', new OfflineConfig())
            ->getPayum()
            ->getGateway('offline');

        $payment = $this->payment();
        $payment->setDetails([
            'paid' => true,
            'status' => 'captured',
        ]);

        $gateway->execute($status = new GetHumanStatus($payment));

        $this->assertTrue($status->isCaptured());
    }

    private function payment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);

        return $payment;
    }
}

final class StoreCreditLedger
{
    public function __construct(
        public int $balance,
    ) {
    }
}

final class StoreCreditCaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly StoreCreditLedger $ledger,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $this->ledger->balance -= (int) $context->payment()?->getTotalAmount();

        return CaptureResult::captured();
    }
}

final class OfflineNotifyHandler implements NotifyHandlerInterface
{
    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::unverified([]);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::ignored();
    }
}
