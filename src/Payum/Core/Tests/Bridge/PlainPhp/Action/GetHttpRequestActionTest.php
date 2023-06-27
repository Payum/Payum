<?php

namespace Payum\Core\Tests\Bridge\PlainPhp\Action;

use Iterator;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;
use stdClass;

class GetHttpRequestActionTest extends GenericActionTest
{
    /**
     * @var class-string<GetHttpRequest>
     */
    protected $requestClass = GetHttpRequest::class;

    /**
     * @var class-string<GetHttpRequestAction>
     */
    protected $actionClass = GetHttpRequestAction::class;

    /**
     * @return \Iterator<GetHttpRequest[]>
     */
    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass()];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldFillRequestDetails(): void
    {
        $action = new GetHttpRequestAction();

        $action->execute($httpRequest = new GetHttpRequest());

        $this->assertSame('GET', $httpRequest->method);
    }
}
