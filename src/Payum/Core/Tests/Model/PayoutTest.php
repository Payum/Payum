<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Payout;
use Payum\Core\Model\PayoutInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PayoutTest extends TestCase
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
}
