<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Legacy\ResultToReply;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class ResultToReplyTest extends TestCase
{
    public function testShouldTurnAnAcknowledgementIntoTheReplyAOneXScriptCatches(): void
    {
        $reply = ResultToReply::translate(
            NotifyResult::handled(PaymentStatus::Captured, new Acknowledgement(200, 'OK', [
                'X-Acme' => '1',
            ]))
        );

        $this->assertInstanceOf(HttpResponse::class, $reply);
        $this->assertSame('OK', $reply->getContent());
        $this->assertSame(200, $reply->getStatusCode());
        $this->assertSame([
            'X-Acme' => '1',
        ], $reply->getHeaders());
    }

    public function testShouldProduceNoReplyForANotifyThatNamedNoAcknowledgement(): void
    {
        // A 1.x notify script reads "nothing thrown" as 204, which is the same answer.
        $this->assertNull(ResultToReply::translate(NotifyResult::handled(PaymentStatus::Captured)));
    }

    public function testShouldProduceNoReplyForAFinishedOperation(): void
    {
        $this->assertNull(ResultToReply::translate(CaptureResult::captured('txn_1')));
    }
}
