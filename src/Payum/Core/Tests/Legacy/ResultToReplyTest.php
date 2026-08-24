<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Exception\LogicException;
use Payum\Core\Legacy\LegacyReply;
use Payum\Core\Legacy\ResultToReply;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\Challenge;
use Payum\Core\Result\NextAction\Poll;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Template\RendererInterface;
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

    public function testShouldSendTheCustomerToClearAChallengeTheWayOneXWouldHave(): void
    {
        $reply = ResultToReply::translate(CaptureResult::pending(new Challenge('https://acs.test/3ds', [
            'creq' => 'abc',
        ], '2.2.0')));

        $this->assertInstanceOf(HttpPostRedirect::class, $reply);
        $this->assertSame('https://acs.test/3ds', $reply->getUrl());
        $this->assertSame([
            'creq' => 'abc',
        ], $reply->getFields());
    }

    public function testShouldSendTheCustomerToAChallengeThatNeedsNoParameters(): void
    {
        $reply = ResultToReply::translate(CaptureResult::pending(new Challenge('https://acs.test/3ds')));

        $this->assertInstanceOf(HttpRedirect::class, $reply);
        $this->assertSame('https://acs.test/3ds', $reply->getUrl());
    }

    public function testShouldProduceNoReplyForAPoll(): void
    {
        // There is nothing to show the customer. A 1.x caller goes to the after url and reads the
        // status, which says pending -- which is the answer.
        $this->assertNull(ResultToReply::translate(CaptureResult::pending(new Poll(30))));
    }

    public function testShouldRenderATemplateWhenGivenARenderer(): void
    {
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->method('render')
            ->with('@PayumAcme/pay.html.twig', [
                'amount' => 100,
            ])
            ->willReturn('<h1>Pay</h1>');

        $reply = ResultToReply::translate(CaptureResult::pending(new RenderTemplate('@PayumAcme/pay.html.twig', [
            'amount' => 100,
        ])), $renderer);

        $this->assertInstanceOf(HttpResponse::class, $reply);
        $this->assertSame('<h1>Pay</h1>', $reply->getContent());
    }

    public function testShouldSayWhatIsMissingForATemplateWithNoRenderer(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(RendererInterface::class);

        ResultToReply::translate(CaptureResult::pending(new RenderTemplate('@PayumAcme/pay.html.twig')));
    }

    public function testShouldGiveBackTheVeryReplyAGatewayThrew(): void
    {
        $thrown = new HttpResponse('<h1>Pay</h1>');

        $reply = ResultToReply::translate(CaptureResult::pending(new LegacyReply($thrown)));

        $this->assertSame($thrown, $reply);
    }

    public function testShouldSayThatANextActionOfSomeoneElsesHasNoReply(): void
    {
        $next = new class() implements NextAction {
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('has no 1.x reply to become');

        ResultToReply::translate(CaptureResult::pending($next));
    }
}
