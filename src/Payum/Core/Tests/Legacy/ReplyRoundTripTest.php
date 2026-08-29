<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Legacy\ReplyToNextAction;
use Payum\Core\Legacy\ResultToReply;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * A reply that goes across the boundary and back must come back the reply it was. Anything less means
 * one of the two APIs quietly drops what the other said.
 */
final class ReplyRoundTripTest extends TestCase
{
    /**
     * @return iterable<string, array{ReplyInterface}>
     */
    public static function replies(): iterable
    {
        yield 'redirect' => [new HttpRedirect('https://acme.test/pay', 301, [
            'X-Acme' => '1',
        ])];

        yield 'post redirect' => [new HttpPostRedirect('https://acme.test/pay', [
            'token' => 'abc',
            'amount' => 100,
        ])];

        yield 'response' => [new HttpResponse('<h1>Pay</h1>', 201, [
            'X-Acme' => '1',
        ])];

        yield 'symfony response' => [new SymfonyHttpResponse(new Response('<h1>Pay</h1>'))];

        yield 'a reply of the gateway\'s own' => [new class() extends LogicException implements ReplyInterface {
        }];
    }

    /**
     * @return iterable<string, array{NextAction}>
     */
    public static function nextActions(): iterable
    {
        yield 'redirect' => [new Redirect('https://acme.test/pay', 301, [
            'X-Acme' => '1',
        ])];

        yield 'post redirect' => [new PostRedirect('https://acme.test/pay', [
            'token' => 'abc',
        ])];
    }

    /**
     * @dataProvider replies
     */
    public function testShouldGetBackTheReplyItStartedFrom(ReplyInterface $reply): void
    {
        $result = CaptureResult::pending(ReplyToNextAction::translate($reply));

        $this->assertEquals($reply, ResultToReply::translate($result));
    }

    public function testShouldGetBackTheAnswerAOneXNotifyActionGaveThePsp(): void
    {
        $reply = new HttpResponse('[accepted]', 200, [
            'X-Acme' => '1',
        ]);

        $result = NotifyResult::handled(PaymentStatus::Captured, ReplyToNextAction::acknowledgement($reply));

        $this->assertEquals($reply, ResultToReply::translate($result));
    }

    /**
     * @dataProvider nextActions
     */
    public function testShouldGetBackTheNextActionItStartedFrom(NextAction $next): void
    {
        $reply = ResultToReply::translate(CaptureResult::pending($next));

        $this->assertInstanceOf(ReplyInterface::class, $reply);
        $this->assertEquals($next, ReplyToNextAction::translate($reply));
    }
}
