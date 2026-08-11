<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\PayoutCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\PayoutHandlerInterface;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Payout is the one operation that does not act on a payment, so it is what proves core works against any
 * subject rather than only against payments.
 */
final class PayoutTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testShouldExerciseThePayoutCapability(): void
    {
        $this->assertSame(Capability::Payout, PayoutCommand::capability());
    }

    public function testShouldCarryThePayoutAsItsSubject(): void
    {
        $payout = new Payout();

        $command = PayoutCommand::forPayout($payout);

        $this->assertSame($payout, $command->payout());
        $this->assertSame($payout, $command->subject());
    }

    public function testShouldRefuseToBeBuiltWithNothingToPayOut(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs either a token or a payout');

        new PayoutCommand();
    }

    public function testAPayoutIsASubject(): void
    {
        $this->assertInstanceOf(SubjectInterface::class, new Payout());
        $this->assertInstanceOf(SubjectInterface::class, new Payment());
    }

    public function testShouldDispatchAPayoutToItsHandler(): void
    {
        $payout = $this->buildPayout();

        $result = $this->buildPayum()->getGateway('acme')->execute(PayoutCommand::forPayout($payout));

        $this->assertInstanceOf(PayoutResult::class, $result);
        $this->assertSame(PaymentStatus::PaidOut, $result->status);
        $this->assertSame(1500, $result->paidOutAmount);
    }

    public function testShouldGiveTheHandlerThePayoutThroughTheContext(): void
    {
        $payout = $this->buildPayout();

        $this->buildPayum()->getGateway('acme')->execute(PayoutCommand::forPayout($payout));

        $this->assertSame('payee@example.com', AcmePayoutHandler::$seenRecipient);
        // A payout is not a payment, so the narrower accessor says so rather than guessing.
        $this->assertNull(AcmePayoutHandler::$seenPayment);
    }

    public function testShouldWriteStateOntoThePayout(): void
    {
        $payout = $this->buildPayout();

        $this->buildPayum()->getGateway('acme')->execute(PayoutCommand::forPayout($payout));

        // The same persistence path a payment takes, against a model that is not one.
        $this->assertSame([
            'batch_id' => 'batch_1',
        ], $payout->getDetails());
    }

    public function testShouldCancelAPayout(): void
    {
        $payout = new Payout();

        $command = CancelCommand::forPayout($payout);

        // Cancelling is the same operation whatever it cancels, so it takes either.
        $this->assertSame($payout, $command->payout());
        $this->assertSame($payout, $command->subject());
        $this->assertNotInstanceOf(PaymentInterface::class, $command->payment());
    }

    private function buildPayout(): Payout
    {
        $payout = new Payout();
        $payout->setRecipientEmail('payee@example.com');
        $payout->setTotalAmount(1500);
        $payout->setCurrencyCode('EUR');

        return $payout;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        AcmePayoutHandler::$seenRecipient = null;
        AcmePayoutHandler::$seenPayment = null;

        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmePayoutConfig())
            ->getPayum();
    }
}

final class AcmePayoutConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmePayoutGateway::class;
    }
}

final class AcmePayoutGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmePayoutConfig::class;
    }

    public function handlers(): array
    {
        return [AcmePayoutHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Payouts';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmePayoutHandler implements PayoutHandlerInterface
{
    public static ?string $seenRecipient = null;

    public static mixed $seenPayment = null;

    public function handle(PayoutCommand $command, Context $context): PayoutResult
    {
        self::$seenRecipient = $context->payout()?->getRecipientEmail();
        self::$seenPayment = $context->payment();

        $state = $context->state();

        // The re-entrancy guard a real payout uses: do not send it twice.
        if ($state['batch_id']) {
            return PayoutResult::paidOut($state['batch_id'], $context->payout()?->getTotalAmount());
        }

        $state['batch_id'] = 'batch_1';

        return PayoutResult::paidOut('batch_1', $context->payout()?->getTotalAmount());
    }
}
