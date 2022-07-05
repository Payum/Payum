<?php

namespace Payum\Core\Tests\Bridge\PlainPhp\Action;

use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;

class GetHttpRequestActionTest extends GenericActionTest
{
    protected $requestClass = \Payum\Core\Request\GetHttpRequest::class;

    protected $actionClass = \Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass()];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [$this->getMockForAbstractClass(\Payum\Core\Request\Generic::class, [[]])];
    }

    public function testShouldFillRequestDetails()
    {
        $action = new GetHttpRequestAction();

        $action->execute($httpRequest = new GetHttpRequest());

        $this->assertSame('GET', $httpRequest->method);
    }
}
