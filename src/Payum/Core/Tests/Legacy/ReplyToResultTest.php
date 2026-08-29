<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Legacy\ReplyToResult;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use PHPUnit\Framework\TestCase;

final class ReplyToResultTest extends TestCase
{
    public function testShouldTurnARedirectReplyIntoARedirect(): void
    {
        $next = ReplyToResult::translate(new HttpRedirect('https://psp.test/checkout', 303));

        $this->assertInstanceOf(Redirect::class, $next);
        $this->assertSame('https://psp.test/checkout', $next->url);
        $this->assertSame(303, $next->statusCode);
    }

    public function testShouldTurnAPostRedirectReplyIntoAPostRedirect(): void
    {
        // HttpPostRedirect extends HttpResponse, so this is also the test that the specific reply is
        // recognised before the general one.
        $next = ReplyToResult::translate(new HttpPostRedirect('https://psp.test/pay', [
            'token' => 'tok_1',
        ]));

        $this->assertInstanceOf(PostRedirect::class, $next);
        $this->assertSame('https://psp.test/pay', $next->url);
        $this->assertSame([
            'token' => 'tok_1',
        ], $next->fields);
    }

    public function testShouldTranslateNoRenderedResponse(): void
    {
        // A rendered page is not intent, and there is no next action to make it one.
        $this->assertNull(ReplyToResult::translate(new HttpResponse('<form>')));
    }
}
