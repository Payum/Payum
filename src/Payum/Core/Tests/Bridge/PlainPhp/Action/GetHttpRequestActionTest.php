<?php
namespace Payum\Core\Tests\Bridge\PlainPhp\Action;

use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;

class GetHttpRequestActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\GetHttpRequest';

    protected $actionClass = 'Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction';

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass());
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
    }

    public function testShouldFillRequestDetails()
    {
        $action = new GetHttpRequestAction();

        $action->execute($httpRequest = new GetHttpRequest());

        $this->assertSame('GET', $httpRequest->method);
    }
}
