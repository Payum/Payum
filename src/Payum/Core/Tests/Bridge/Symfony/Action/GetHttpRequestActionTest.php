<?php

namespace Payum\Core\Tests\Bridge\Symfony\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Symfony\Action\GetHttpRequestAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GetHttpRequestActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(GetHttpRequestAction::class);

        self::assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportGetHttpRequest()
    {
        $action = new GetHttpRequestAction();

        self::assertTrue($action->supports(new GetHttpRequest()));
    }

    public function testShouldNotSupportAnythingNotGetHttpRequest()
    {
        $action = new GetHttpRequestAction();

        self::assertFalse($action->supports('foo'));
    }

    public function testThrowIfNotSupportedRequestPassedToExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action GetHttpRequestAction is not supported the request string.');
        $action = new GetHttpRequestAction();

        $action->execute('foo');
    }

    public function testShouldDoNothingIfHttpRequestNotSet()
    {
        $action = new GetHttpRequestAction();

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('', $request->method);
        self::assertSame('', $request->uri);
    }

    public function testShouldDoNothingIfHttpRequestStackIsEmpty()
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

    public function testShouldPopulateFromGetMainRequestOnStack()
    {
        $stack = new RequestStack();
        $stack->push(Request::create(
            'http://request.uri',
            'GET',
            [
                'foo' => 'fooVal',
            ]
        ));

        $action = new GetHttpRequestAction();
        $action->setHttpRequestStack($stack);

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([
            'foo' => 'fooVal',
        ], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('GET', $request->method);
        self::assertSame('http://request.uri/?foo=fooVal', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }

    public function testShouldPopulateFromPostMainRequestOnStack()
    {
        $stack = new RequestStack();
        $stack->push(Request::create(
            'http://request.uri',
            'POST',
            [
                'foo' => 'fooVal',
            ]
        ));

        $action = new GetHttpRequestAction();
        $action->setHttpRequestStack($stack);

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame([
            'foo' => 'fooVal',
        ], $request->request);
        self::assertSame('POST', $request->method);
        self::assertSame('http://request.uri/', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }

    public function testShouldPopulateFromMainRequestIgnoringSubRequestsOnStack()
    {
        $stack = new RequestStack();
        $stack->push(Request::create(
            'http://request.uri',
            'GET',
            [
                'foo' => 'fooVal',
            ]
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
     */
    public function testShouldPopulateFromGetHttpRequest()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequest(Request::create(
            'http://request.uri',
            'GET',
            [
                'foo' => 'fooVal',
            ]
        ));

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([
            'foo' => 'fooVal',
        ], $request->query);
        self::assertSame([], $request->request);
        self::assertSame('GET', $request->method);
        self::assertSame('http://request.uri/?foo=fooVal', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @deprecated
     */
    public function testShouldPopulateFromPostHttpRequest()
    {
        $action = new GetHttpRequestAction();
        $action->setHttpRequest(Request::create(
            'http://request.uri',
            'POST',
            [
                'foo' => 'fooVal',
            ]
        ));

        $request = new GetHttpRequest();
        $action->execute($request);

        self::assertSame([], $request->query);
        self::assertSame([
            'foo' => 'fooVal',
        ], $request->request);
        self::assertSame('POST', $request->method);
        self::assertSame('http://request.uri/', $request->uri);
        self::assertStringStartsWith('Symfony', $request->userAgent);
        self::assertSame('127.0.0.1', $request->clientIp);
    }
}
