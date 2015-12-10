<?php
namespace Payum\Core\Tests\Bridge\Symfony\Action\Http;

use Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpFoundation\Request;

class GetHttpRequestActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetHttpRequestAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetHttpReqeust()
    {
        $expectedRequest = new Request();

        $action = new GetHttpRequestAction();
        $action->setHttpRequest($expectedRequest);

        $this->assertAttributeSame($expectedRequest, 'httpRequest', $action);
    }

    /**
     * @test
     */
    public function shouldSupportGetHttpRequest()
    {
        $action = new GetHttpRequestAction();

        $this->assertTrue($action->supports(new GetHttpRequest()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetHttpRequest()
    {
        $action = new GetHttpRequestAction();

        $this->assertFalse($action->supports('foo'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action GetHttpRequestAction is not supported the request string.
     */
    public function throwIfNotSupportedRequestPassedToExecute()
    {
        $action = new GetHttpRequestAction();

        $action->execute('foo');
    }

    /**
     * @test
     */
    public function shouldDoNothingIfHttpRequestNotSet()
    {
        $action = new GetHttpRequestAction();

        $request = new \Payum\Core\Request\GetHttpRequest();
        $action->execute($request);

        $this->assertSame(array(), $request->query);
        $this->assertSame(array(), $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }

    /**
     * @test
     */
    public function shouldPopulateFromGetHttpRequest()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequest(Request::create(
            'http://request.uri',
            'GET',
            array('foo' => 'fooVal')
        ));

        $request = new GetHttpRequest();
        $action->execute($request);

        $this->assertSame(array('foo' => 'fooVal'), $request->query);
        $this->assertSame(array(), $request->request);
        $this->assertSame('GET', $request->method);
        $this->assertSame('http://request.uri/?foo=fooVal', $request->uri);
        $this->assertSame('Symfony/2.X', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @test
     */
    public function shouldPopulateFromPostHttpRequest()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequest(Request::create(
            'http://request.uri',
            'POST',
            array('foo' => 'fooVal')
        ));

        $request = new \Payum\Core\Request\GetHttpRequest();
        $action->execute($request);

        $this->assertSame(array(), $request->query);
        $this->assertSame(array('foo' => 'fooVal'), $request->request);
        $this->assertSame('POST', $request->method);
        $this->assertSame('http://request.uri/', $request->uri);
        $this->assertSame('Symfony/2.X', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }
}
