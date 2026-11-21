<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\ChargeReason;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\Initiator;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Gateway as Executor;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class StoredCredentialTest extends TestCase
{
    public function testATokenMeansTheCustomerIsPresent(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $credential = $this->contextFor(CaptureCommand::forToken($token), $token, 'pm_1')->storedCredential();

        $this->assertSame(Initiator::Customer, $credential->initiator);
    }

    public function testNoTokenMeansMerchantInitiated(): void
    {
        $credential = $this->contextFor(CaptureCommand::forPayment(new Payment()), null, 'pm_1')->storedCredential();

        $this->assertSame(Initiator::Merchant, $credential->initiator);
    }

    public function testTheCommandCanOverrideTheInitiator(): void
    {
        $command = CaptureCommand::forPayment(new Payment(), initiator: Initiator::Customer, reason: ChargeReason::Installment);
        $credential = $this->contextFor($command, null, 'pm_1')->storedCredential();

        $this->assertSame(Initiator::Customer, $credential->initiator);
        $this->assertSame(ChargeReason::Installment, $credential->reason);
    }

    public function testAMethodWithoutAReferenceIsTheFirstUse(): void
    {
        $first = $this->contextFor(AuthorizeCommand::forPayment(new Payment()), null, null)->storedCredential();
        $later = $this->contextFor(AuthorizeCommand::forPayment(new Payment()), null, 'pm_1', 'nt_9')->storedCredential();

        $this->assertTrue($first->isFirst);
        $this->assertFalse($later->isFirst);
        $this->assertSame('nt_9', $later->previousNetworkTransactionId);
    }

    public function testNoStoredMethodMeansNoStoredCredential(): void
    {
        $context = $this->build(CaptureCommand::forPayment(new Payment()), new Payment());
        $this->assertNull($context->storedCredential());
        $this->assertNull($context->storedPaymentMethod());
        $this->assertNull($context->customer());

        $this->assertNull($this->contextFor(new SyncCommand(subject: new Payment()), null, 'pm_1')->storedCredential());
    }

    public function testTheReasonDefaultsToUnscheduled(): void
    {
        $credential = $this->contextFor(CaptureCommand::forPayment(new Payment()), null, 'pm_1')->storedCredential();

        $this->assertSame(ChargeReason::Unscheduled, $credential->reason);
    }

    public function testTheCustomerComesFromTheSubjectOrItsMethod(): void
    {
        $customer = new Customer();
        $method = $this->method('pm_1', customer: $customer);

        $this->assertSame($customer, $this->build(CaptureCommand::forPayment(new Payment()), $method)->customer());

        $payment = new Payment();
        $payment->setCustomer($customer);
        $payment->setStoredPaymentMethod($method);
        $this->assertSame($customer, $this->build(CaptureCommand::forPayment($payment), $payment)->customer());
        $this->assertSame($method, $this->build(CaptureCommand::forPayment($payment), $payment)->storedPaymentMethod());
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function contextFor(CommandInterface $command, ?TokenInterface $token, ?string $reference, ?string $networkId = null): Context
    {
        $payment = new Payment();
        $payment->setStoredPaymentMethod($this->method($reference, $networkId));

        return $this->build($command, $payment, $token);
    }

    private function method(?string $reference, ?string $networkId = null, ?Customer $customer = null): StoredPaymentMethodInterface
    {
        $method = $this->createMock(StoredPaymentMethodInterface::class);
        $method->method('getReference')->willReturn($reference);
        $method->method('getNetworkTransactionId')->willReturn($networkId);
        $method->method('getCustomer')->willReturn($customer);

        return $method;
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function build(CommandInterface $command, object $subject, ?TokenInterface $token = null): Context
    {
        return new Context(
            $this->createMock(Executor::class),
            $command,
            $this->createMock(PaymentGateway::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(TokenIssuerInterface::class),
            $subject,
            $token,
        );
    }
}
