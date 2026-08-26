<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Command;

use Closure;
use Money\Currency;
use Money\Money;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

/**
 * The three commands that can act on part of a payment all answer the same way about how much.
 */
final class AmountCarryingCommandTest extends TestCase
{
    /**
     * @return iterable<string, array{Closure(PaymentInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand), Closure(TokenInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand)}>
     */
    public function commands(): iterable
    {
        yield 'capture' => [
            static fn (PaymentInterface $payment, int|Money|null $amount): CaptureCommand => CaptureCommand::forPayment($payment, $amount),
            static fn (TokenInterface $token, int|Money|null $amount): CaptureCommand => CaptureCommand::forToken($token, $amount),
        ];

        yield 'authorize' => [
            static fn (PaymentInterface $payment, int|Money|null $amount): AuthorizeCommand => AuthorizeCommand::forPayment($payment, $amount),
            static fn (TokenInterface $token, int|Money|null $amount): AuthorizeCommand => AuthorizeCommand::forToken($token, $amount),
        ];

        yield 'refund' => [
            static fn (PaymentInterface $payment, int|Money|null $amount): RefundCommand => RefundCommand::forPayment($payment, $amount),
            static fn (TokenInterface $token, int|Money|null $amount): RefundCommand => RefundCommand::forToken($token, $amount),
        ];
    }

    /**
     * @dataProvider commands
     *
     * @param Closure(PaymentInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand) $forPayment
     */
    public function testShouldCarryAMoneyUnchanged(Closure $forPayment): void
    {
        $command = $forPayment($this->payment(), Money::JPY(500));

        $this->assertTrue(Money::JPY(500)->equals($command->money()));
        $this->assertSame(500, $command->amount);
    }

    /**
     * @dataProvider commands
     *
     * @param Closure(PaymentInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand) $forPayment
     */
    public function testShouldReadMinorUnitsInThePaymentsCurrency(Closure $forPayment): void
    {
        $command = $forPayment($this->payment(), 500);

        $this->assertTrue(Money::USD(500)->equals($command->money()));
        $this->assertSame(500, $command->amount);
    }

    /**
     * @dataProvider commands
     *
     * @param Closure(PaymentInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand) $forPayment
     * @param Closure(TokenInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand) $forToken
     */
    public function testShouldReadMinorUnitsInTheCurrencyItIsGivenWhenItCarriesOnlyAToken(Closure $forPayment, Closure $forToken): void
    {
        $command = $forToken($this->createMock(TokenInterface::class), 500);

        // Nothing to take a currency from until core has resolved the token's subject.
        $this->assertNull($command->money());
        $this->assertTrue(Money::EUR(500)->equals($command->money(new Currency('EUR'))));
    }

    /**
     * @dataProvider commands
     *
     * @param Closure(PaymentInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand) $forPayment
     */
    public function testShouldCarryNoAmountAtAllForTheWholePayment(Closure $forPayment): void
    {
        $command = $forPayment($this->payment(), null);

        $this->assertNull($command->amount);
        $this->assertNull($command->money());
        $this->assertNull($command->money(new Currency('EUR')));
    }

    /**
     * @dataProvider commands
     *
     * @param Closure(PaymentInterface, int|Money|null): (CaptureCommand|AuthorizeCommand|RefundCommand) $forPayment
     */
    public function testShouldPreferItsOwnMoneyOverTheCurrencyItIsOffered(Closure $forPayment): void
    {
        $command = $forPayment($this->payment(), Money::JPY(500));

        $this->assertTrue(Money::JPY(500)->equals($command->money(new Currency('EUR'))));
    }

    private function payment(): Payment
    {
        $payment = new Payment();
        $payment->setTotalAmount(1000);
        $payment->setCurrencyCode('USD');

        return $payment;
    }
}
