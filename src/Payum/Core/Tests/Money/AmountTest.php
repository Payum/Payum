<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Money;

use Money\Currency;
use Money\Money;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Money\Amount;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AmountTest extends TestCase
{
    public function testShouldReadAPaymentThatKnowsNothingOfMoney(): void
    {
        // A 1.x entity mapped to PaymentInterface, which is what most applications still have.
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getTotalAmount')->willReturn(123);
        $payment->method('getCurrencyCode')->willReturn('USD');

        $this->assertTrue(Money::USD(123)->equals(Amount::of($payment)));
    }

    public function testShouldReadAPayout(): void
    {
        $payout = new Payout();
        $payout->setTotalAmount(4500);
        $payout->setCurrencyCode('EUR');

        $this->assertTrue(Money::EUR(4500)->equals(Amount::of($payout)));
    }

    public function testShouldPreferTheMoneyAccessorWhenThereIsOne(): void
    {
        $payment = new Payment();
        $payment->setMoney(Money::JPY(500));

        $this->assertTrue(Money::JPY(500)->equals(Amount::of($payment)));
    }

    public function testShouldTreatAnAmountWithNoCurrencyAsNoMoney(): void
    {
        $payment = new Payment();
        $payment->setTotalAmount(123);

        $this->assertNull(Amount::of($payment));
    }

    public function testShouldTreatACurrencyWithNoAmountAsNoMoney(): void
    {
        $payment = new Payment();
        $payment->setCurrencyCode('USD');

        $this->assertNull(Amount::of($payment));
    }

    public function testShouldReadNothingOffSomethingThatIsNotASubject(): void
    {
        $this->assertNull(Amount::of(new stdClass()));
        $this->assertNull(Amount::of(null));
    }

    public function testShouldWriteThroughTheMoneyAccessor(): void
    {
        $payment = new Payment();

        Amount::assign($payment, Money::GBP(999));

        $this->assertSame(999, $payment->getTotalAmount());
        $this->assertSame('GBP', $payment->getCurrencyCode());
    }

    public function testShouldWriteThroughThePlainAccessors(): void
    {
        $payment = new class() {
            public ?int $totalAmount = null;

            public ?string $currencyCode = null;

            public function setTotalAmount(?int $totalAmount): void
            {
                $this->totalAmount = $totalAmount;
            }

            public function setCurrencyCode(?string $currencyCode): void
            {
                $this->currencyCode = $currencyCode;
            }
        };

        Amount::assign($payment, Money::GBP(999));

        $this->assertSame(999, $payment->totalAmount);
        $this->assertSame('GBP', $payment->currencyCode);
    }

    public function testShouldClearBothHalvesWhenAssignedNothing(): void
    {
        $payment = new Payment();
        $payment->setMoney(Money::GBP(999));

        Amount::assign($payment, null);

        $this->assertNull($payment->getTotalAmount());
        $this->assertNull($payment->getCurrencyCode());
    }

    public function testShouldRefuseToWriteToAModelWithNowhereToPutIt(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('has nowhere to put an amount');

        Amount::assign(new stdClass(), Money::USD(1));
    }

    public function testShouldFormatAnIsoCurrencyToItsOwnNumberOfPlaces(): void
    {
        // JPY has none and KWD has three, which is the case a hardcoded exponent of 2 gets wrong.
        $this->assertSame('1.23', Amount::toDecimalString(123, 'USD'));
        $this->assertSame('123', Amount::toDecimalString(123, 'JPY'));
        $this->assertSame('0.123', Amount::toDecimalString(123, 'KWD'));
    }

    public function testShouldFormatACurrencyIso4217DoesNotList(): void
    {
        // The float that dividing by 10 ** 18 produces cannot hold this.
        $this->assertSame('1.234567890123456789', Amount::toDecimalString('1234567890123456789', 'ETH', 18));
    }

    public function testShouldFormatNothingAsZero(): void
    {
        $this->assertSame('0.00', Amount::toDecimalString(null, 'USD'));
    }

    public function testShouldRefuseAnAmountTooLargeForAnInteger(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('does not fit in an integer');

        Amount::toMinorUnits(new Money('99999999999999999999', new Currency('ETH')));
    }
}
