<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\Command\PayoutCommand;
use Payum\Core\Model\Payout;
use Payum\Core\Result\PaymentStatus;

final class PayoutHandlerTest extends OfflineHandlerTestCase
{
    public function testShouldPayOutAndWriteThePayoutToTheDetails(): void
    {
        $payout = $this->payout();

        $result = $this->gateway()->execute(PayoutCommand::forPayout($payout));

        $this->assertSame(PaymentStatus::PaidOut, $result->status);
        $this->assertSame([
            'amount' => 500,
            'currency' => 'EUR',
            'description' => 'Commission',
            'recipient_email' => 'seller@example.com',
            'recipient_id' => 'seller-1',
            'payout' => true,
            'status' => 'payedout',
        ], (array) $payout->getDetails());
    }

    public function testShouldLeaveAPayoutNotYetMadePending(): void
    {
        $payout = $this->payout();
        $payout->setDetails([
            'payout' => false,
        ]);

        $result = $this->gateway()->execute(PayoutCommand::forPayout($payout));

        $this->assertSame(PaymentStatus::Pending, $result->status);
    }

    private function payout(): Payout
    {
        $payout = new Payout();
        $payout->setCurrencyCode('EUR');
        $payout->setTotalAmount(500);
        $payout->setDescription('Commission');
        $payout->setRecipientEmail('seller@example.com');
        $payout->setRecipientId('seller-1');

        return $payout;
    }
}
