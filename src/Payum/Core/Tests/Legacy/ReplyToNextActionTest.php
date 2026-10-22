<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Exception\LogicException;
use Payum\Core\Legacy\LegacyReply;
use Payum\Core\Legacy\ReplyToNextAction;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use PHPUnit\Framework\TestCase;

final class ReplyToNextActionTest extends TestCase
{
    public function testShouldTurnARedirectReplyIntoARedirect(): void
    {
        $next = ReplyToNextAction::translate(new HttpRedirect('https://acme.test/pay', 301, [
            'X-Acme' => '1',
        ]));

        $this->assertInstanceOf(Redirect::class, $next);
        $this->assertSame('https://acme.test/pay', $next->url);
        $this->assertSame(301, $next->statusCode);
        $this->assertSame([
            'X-Acme' => '1',
        ], $next->headers);
    }

    public function testShouldDropTheLocationHeaderTheReplyDerivesFromTheUrl(): void
    {
        // Keeping it would make the next action carry the url twice, and translating back would then
        // produce a reply that is not the one we started from.
        $next = ReplyToNextAction::translate(new HttpRedirect('https://acme.test/pay'));

        $this->assertInstanceOf(Redirect::class, $next);
        $this->assertSame([], $next->headers);
    }

    public function testShouldTurnAPostRedirectReplyIntoAPostRedirect(): void
    {
        $next = ReplyToNextAction::translate(new HttpPostRedirect('https://acme.test/pay', [
            'token' => 'abc',
        ]));

        $this->assertInstanceOf(PostRedirect::class, $next);
        $this->assertSame('https://acme.test/pay', $next->url);
        $this->assertSame([
            'token' => 'abc',
        ], $next->fields);
    }

    public function testShouldCarryARenderedResponseAcrossUntouched(): void
    {
        // The body is already markup: whichever template produced it is not in the reply any more.
        $reply = new HttpResponse('<h1>Pay</h1>', 201, [
            'X-Acme' => '1',
        ]);

        $next = ReplyToNextAction::translate($reply);

        $this->assertInstanceOf(LegacyReply::class, $next);
        $this->assertSame($reply, $next->reply);
    }

    public function testShouldCarryAReplyOfItsOwnAcrossUntouched(): void
    {
        $reply = new class() extends LogicException implements ReplyInterface {
        };

        $next = ReplyToNextAction::translate($reply);

        $this->assertInstanceOf(LegacyReply::class, $next);
        $this->assertSame($reply, $next->reply);
    }

    public function testShouldProduceNoNextActionWhenTheActionThrewNothing(): void
    {
        $this->assertNull(ReplyToNextAction::translate(null));
    }

    public function testShouldTurnTheAnswerAOneXNotifyActionThrewIntoAnAcknowledgement(): void
    {
        $acknowledgement = ReplyToNextAction::acknowledgement(new HttpResponse('[accepted]', 200, [
            'X-Acme' => '1',
        ]));

        $this->assertInstanceOf(Acknowledgement::class, $acknowledgement);
        $this->assertSame(200, $acknowledgement->status);
        $this->assertSame('[accepted]', $acknowledgement->body);
        $this->assertSame([
            'X-Acme' => '1',
        ], $acknowledgement->headers);
    }
}
