<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Model;

use Money\Money;
use Payum\Core\Model\MoneyAwareInterface;
use Payum\Core\Model\Payout;
use Payum\Core\Model\PayoutInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PayoutTest extends TestCase
{
    public function testShouldExtendPayoutInterface(): void
    {
        $rc = new ReflectionClass(Payout::class);

        $this->assertTrue($rc->implementsInterface(PayoutInterface::class));
    }

    public function testShouldAllowGetRecipientIdPreviouslySet(): void
    {
        $payout = new Payout();
        $payout->setRecipientId('theVal');

        $this->assertSame('theVal', $payout->getRecipientId());
    }

    public function testShouldAllowGetRecipientEmailPreviouslySet(): void
    {
        $payout = new Payout();
        $payout->setRecipientEmail('theVal');

        $this->assertSame('theVal', $payout->getRecipientEmail());
    }

    public function testShouldAllowGetTotalAmountPreviouslySet(): void
    {
        $payout = new Payout();
        $payout->setTotalAmount('theVal');

        $this->assertSame('theVal', $payout->getTotalAmount());
    }

    public function testShouldAllowGetCurrencyCodePreviouslySet(): void
    {
        $payout = new Payout();
        $payout->setCurrencyCode('theVal');

        $this->assertSame('theVal', $payout->getCurrencyCode());
    }

    public function testShouldAllowGetDescriptionPreviouslySet(): void
    {
        $payout = new Payout();
        $payout->setDescription('theVal');

        $this->assertSame('theVal', $payout->getDescription());
    }

    public function testShouldAllowGetDetailsPreviouslySet(): void
    {
        $payout = new Payout();

        $payout->setDetails([
            'foo' => 'fooVal',
        ]);

        $this->assertSame([
            'foo' => 'fooVal',
        ], $payout->getDetails());
    }

    public function testShouldImplementsMoneyAwareInterface(): void
    {
        $rc = new ReflectionClass(Payout::class);

        $this->assertTrue($rc->implementsInterface(MoneyAwareInterface::class));
    }

    public function testShouldAllowGetMoneyBuiltFromTheAmountAndCurrencyPreviouslySet(): void
    {
        $payout = new Payout();
        $payout->setTotalAmount(4500);
        $payout->setCurrencyCode('EUR');

        $this->assertTrue(Money::EUR(4500)->equals($payout->getMoney()));
    }

    public function testShouldWriteAMoneyThroughToTheAmountAndCurrency(): void
    {
        $payout = new Payout();

        $payout->setMoney(Money::JPY(500));

        $this->assertSame(500, $payout->getTotalAmount());
        $this->assertSame('JPY', $payout->getCurrencyCode());
    }
}
