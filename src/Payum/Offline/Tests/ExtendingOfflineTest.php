<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Offline\OfflineConfig;
use Payum\Offline\OfflineGateway;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class ExtendingOfflineTest extends TestCase
{
    public function testShouldBuildTheOfflineGatewayFromAConfigThatExtendsOfflineConfig(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(StoreCreditLedger::class, $ledger = new StoreCreditLedger(500))
            ->registerGateway('store_credit', new StoreCreditConfig())
            ->getPayum();

        $result = $payum->getGateway('store_credit')->execute(CaptureCommand::forPayment($this->payment()));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(377, $ledger->balance);
    }

    public function testShouldHandTheExtendingConfigToTheGatewaysOwnHandlers(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('bank_transfer', new BankTransferConfig('GB33BUKB20201555555555'))
            ->getPayum();

        $result = $payum->getGateway('bank_transfer')->execute(CaptureCommand::forPayment($this->payment()));

        $this->assertSame(PaymentStatus::Pending, $result->status);
    }

    public function testShouldBuildAGatewayThatExtendsOfflineGateway(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('cash', new CashConfig())
            ->getPayum();

        $this->assertSame('Cash on delivery', (new CashGateway())->name());
        $this->assertContains(CashNotifyHandler::class, (new CashGateway())->handlers());

        $result = $payum->getGateway('cash')->execute(CaptureCommand::forPayment($this->payment()));

        $this->assertSame(PaymentStatus::Captured, $result->status);
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

final class StoreCreditConfig extends OfflineConfig
{
    public function __construct()
    {
        parent::__construct(handlers: [StoreCreditCaptureHandler::class]);
    }
}

final class BankTransferConfig extends OfflineConfig
{
    public function __construct(
        public readonly string $iban,
    ) {
        parent::__construct(paid: false);
    }
}

final class CashConfig extends OfflineConfig
{
    public function getGatewayClass(): string
    {
        return CashGateway::class;
    }
}

final class CashGateway extends OfflineGateway
{
    public function name(): string
    {
        return 'Cash on delivery';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://example.com/cash');
    }

    public function configClass(): string
    {
        return CashConfig::class;
    }

    public function handlers(): array
    {
        return [...parent::handlers(), CashNotifyHandler::class];
    }
}

final class CashNotifyHandler implements NotifyHandlerInterface
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
