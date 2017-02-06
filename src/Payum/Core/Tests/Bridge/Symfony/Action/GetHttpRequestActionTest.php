<?php
namespace Payum\Core\Tests\Bridge\Symfony\Action\Http;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction;
use Payum\Core\Request\GetHttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GetHttpRequestActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetHttpRequestAction::class);

        self::assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetHttpRequestAction();
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldAllowSetHttpRequest()
    {
        $expectedRequest = new Request();

        $action = new GetHttpRequestAction();
        $action->setHttpRequest($expectedRequest);

        self::assertAttributeSame($expectedRequest, 'httpRequest', $action);
    }

    /**
     * @test
     */
    public function shouldSupportGetHttpRequest()
    {
        $action = new GetHttpRequestAction();

        self::assertTrue($action->supports(new GetHttpRequest()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetHttpRequest()
    {
        $action = new GetHttpRequestAction();

        self::assertFalse($action->supports('foo'));
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

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('', $request->method);
        self::assertSame('', $request->uri);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfHttpRequestStackIsEmpty()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequestStack(new RequestStack());

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('', $request->method);
        self::assertSame('', $request->uri);
    }

    /**
     * @test
     */
    public function shouldPopulateFromGetMasterRequestOnStack()
    {
        $stack = new RequestStack();
        $stack->push(Request::create(
            'http://request.uri',
            'GET',
            ['foo' => 'fooVal']
        ));
        
        $action = new GetHttpRequestAction();
        $action->setHttpRequestStack($stack);

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame(['foo' => 'fooVal'], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('GET', $request->method);
        self::assertSame('http://request.uri/?foo=fooVal', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @test
     */
    public function shouldPopulateFromPostMasterRequestOnStack()
    {
        $stack = new RequestStack();
        $stack->push(Request::create(
            'http://request.uri',
            'POST',
            ['foo' => 'fooVal']
        ));
        
        $action = new GetHttpRequestAction();
        $action->setHttpRequestStack($stack);

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame(['foo' => 'fooVal'], $request->request);
        self::assertSame('POST', $request->method);
        self::assertSame('http://request.uri/', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @test
     */
    public function shouldPopulateFromMasterRequestIgnoringSubRequestsOnStack()
    {
        $stack = new RequestStack();
        $stack->push(Request::create(
            'http://request.uri',
            'GET',
            ['foo' => 'fooVal']
        ));
        $stack->push(Request::create(
            'http://another.request.uri',
            'POST'
        ));

        $action = new GetHttpRequestAction();
        $action->setHttpRequestStack($stack);

        $request = new GetHttpRequest();
        $action->execute($request);
        
        self::assertSame('GET', $request->method);
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldPopulateFromGetHttpRequest()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequest(Request::create(
            'http://request.uri',
            'GET',
            ['foo' => 'fooVal']
        ));

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame(['foo' => 'fooVal'], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('GET', $request->method);
        self::assertSame('http://request.uri/?foo=fooVal', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldPopulateFromPostHttpRequest()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequest(Request::create(
            'http://request.uri',
            'POST',
            ['foo' => 'fooVal']
        ));

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame(['foo' => 'fooVal'], $request->request);
        self::assertSame('POST', $request->method);
        self::assertSame('http://request.uri/', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }
}
