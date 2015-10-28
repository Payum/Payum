<?php
namespace Payum\Core\Tests\Bridge\PlainPhp\Action;

use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;

class GetHttpRequestActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\GetHttpRequest';

    protected $actionClass = 'Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass()),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldFillRequestDetails()
    {
        $action = new GetHttpRequestAction();

        $action->execute($httpRequest = new GetHttpRequest());

        $this->assertEquals('GET', $httpRequest->method);
    }
}
