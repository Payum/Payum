<?php
namespace Payum\Core\Tests\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction;
use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GetHttpRequestActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetHttpRequestAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
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
     */
    public function throwIfNotSupportedRequestPassedToExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action GetHttpRequestAction is not supported the request string.');
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

        $this->assertSame([], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
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

        $this->assertSame([], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }

    /**
     * @test
     */
    public function shouldPopulateFromGetMainRequestOnStack()
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

        $this->assertSame(['foo' => 'fooVal'], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('GET', $request->method);
        $this->assertSame('http://request.uri/?foo=fooVal', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @test
     */
    public function shouldPopulateFromPostMainRequestOnStack()
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

        $this->assertSame([], $request->query);
        $this->assertSame(['foo' => 'fooVal'], $request->request);
        $this->assertSame('POST', $request->method);
        $this->assertSame('http://request.uri/', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @test
     */
    public function shouldPopulateFromMainRequestIgnoringSubRequestsOnStack()
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

        $this->assertSame('GET', $request->method);
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

        $this->assertSame(['foo' => 'fooVal'], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('GET', $request->method);
        $this->assertSame('http://request.uri/?foo=fooVal', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
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

        $this->assertSame([], $request->query);
        $this->assertSame(['foo' => 'fooVal'], $request->request);
        $this->assertSame('POST', $request->method);
        $this->assertSame('http://request.uri/', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }
}
