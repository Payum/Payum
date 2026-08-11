<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

final class SyncTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        AcmeSyncHandler::$answer = PaymentStatus::Captured;
    }

    public function testShouldExerciseTheSyncCapability(): void
    {
        $this->assertSame(Capability::Sync, SyncCommand::capability());
    }

    public function testShouldSynchroniseEitherAPaymentOrAPayout(): void
    {
        $payment = new Payment();
        $payout = new Payout();

        $this->assertSame($payment, SyncCommand::forPayment($payment)->subject());
        $this->assertSame($payout, SyncCommand::forPayout($payout)->subject());
        $this->assertNotInstanceOf(PaymentInterface::class, SyncCommand::forPayout($payout)->payment());
    }

    public function testShouldRefuseToBeBuiltWithNothingToSynchronise(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs either a token or something to synchronise');

        new SyncCommand();
    }

    public function testShouldReportWhateverThePspSays(): void
    {
        AcmeSyncHandler::$answer = PaymentStatus::Refunded;

        $result = $this->buildPayum()->getGateway('acme')->execute(SyncCommand::forPayment(new Payment()));

        $this->assertInstanceOf(SyncResult::class, $result);
        $this->assertSame(PaymentStatus::Refunded, $result->status);
    }

    public function testShouldBringTheStoredStatusUpToDate(): void
    {
        $payment = new TrackedSyncPayment();
        $payment->setStatus(PaymentStatus::Pending);

        AcmeSyncHandler::$answer = PaymentStatus::Captured;

        $this->buildPayum()->getGateway('acme')->execute(SyncCommand::forPayment($payment));

        // Catching a status up with the PSP after a webhook that never arrived is the point of a sync.
        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    public function testShouldLeaveTheStoredStatusAloneWhenThePspCouldNotBeAsked(): void
    {
        $payment = new TrackedSyncPayment();
        $payment->setStatus(PaymentStatus::Captured);

        AcmeSyncHandler::$answer = null;

        $this->buildPayum()->getGateway('acme')->execute(SyncCommand::forPayment($payment));

        // A read that failed is not evidence that anything changed.
        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmeSyncConfig())
            ->getPayum();
    }
}

final class AcmeSyncConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeSyncGateway::class;
    }
}

final class AcmeSyncGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeSyncConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeSyncHandler::class];
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

final class AcmeSyncHandler implements SyncHandlerInterface
{
    public static ?PaymentStatus $answer = PaymentStatus::Captured;

    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        if (! self::$answer instanceof PaymentStatus) {
            return SyncResult::failed(new Failure(FailureReason::Network, 'timeout'));
        }

        return SyncResult::synced(self::$answer, 'txn_1', [
            'status' => self::$answer->value,
        ]);
    }
}

class TrackedSyncPayment extends Payment implements StatusAwareInterface
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
