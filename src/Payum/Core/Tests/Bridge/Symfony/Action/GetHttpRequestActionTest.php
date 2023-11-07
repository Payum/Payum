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
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(GetHttpRequestAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportGetHttpRequest(): void
    {
        $action = new GetHttpRequestAction();

        $this->assertTrue($action->supports(new GetHttpRequest()));
    }

    public function testShouldNotSupportAnythingNotGetHttpRequest(): void
    {
        $action = new GetHttpRequestAction();

        $this->assertFalse($action->supports('foo'));
    }

    public function testThrowIfNotSupportedRequestPassedToExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action GetHttpRequestAction is not supported the request string.');
        $action = new GetHttpRequestAction();

        $action->execute('foo');
    }

    public function testShouldDoNothingIfHttpRequestNotSet(): void
    {
        $action = new GetHttpRequestAction();

        $request = new GetHttpRequest();
        $action->execute($request);

        $this->assertSame([], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }

    public function testShouldDoNothingIfHttpRequestStackIsEmpty(): void
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

    public function testShouldPopulateFromGetMainRequestOnStack(): void
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

        $this->assertSame([
            'foo' => 'fooVal',
        ], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('GET', $request->method);
        $this->assertSame('http://request.uri/?foo=fooVal', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }

    public function testShouldPopulateFromPostMainRequestOnStack(): void
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

        $this->assertSame([], $request->query);
        $this->assertSame([
            'foo' => 'fooVal',
        ], $request->request);
        $this->assertSame('POST', $request->method);
        $this->assertSame('http://request.uri/', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }

    public function testShouldPopulateFromMainRequestIgnoringSubRequestsOnStack(): void
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

        $this->assertSame('GET', $request->method);
    }

    /**
     * @deprecated
     */
    public function testShouldPopulateFromGetHttpRequest(): void
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

        $this->assertSame([
            'foo' => 'fooVal',
        ], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('GET', $request->method);
        $this->assertSame('http://request.uri/?foo=fooVal', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }

    /**
     * @deprecated
     */
    public function testShouldPopulateFromPostHttpRequest(): void
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

        $this->assertSame([], $request->query);
        $this->assertSame([
            'foo' => 'fooVal',
        ], $request->request);
        $this->assertSame('POST', $request->method);
        $this->assertSame('http://request.uri/', $request->uri);
        $this->assertStringStartsWith('Symfony', $request->userAgent);
        $this->assertSame('127.0.0.1', $request->clientIp);
    }
}
