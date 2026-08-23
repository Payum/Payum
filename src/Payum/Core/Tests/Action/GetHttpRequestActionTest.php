<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Action;

use Http\Discovery\Psr17FactoryDiscovery;
use Iterator;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use stdClass;

final class GetHttpRequestActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(GetHttpRequestAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportGetHttpRequest(): void
    {
        $action = new GetHttpRequestAction($this->createServerRequest());

        $this->assertTrue($action->supports(new GetHttpRequest()));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testShouldNotSupportAnythingElse(mixed $request): void
    {
        $action = new GetHttpRequestAction($this->createServerRequest());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @dataProvider provideNotSupportedRequests
     */
    public function testThrowIfNotSupportedRequestPassedToExecute(mixed $request): void
    {
        $action = new GetHttpRequestAction($this->createServerRequest());

        $this->expectException(RequestNotSupportedException::class);

        $action->execute($request);
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [new stdClass()];
    }

    public function testShouldFillTheRequestFromThePsrRequest(): void
    {
        $action = new GetHttpRequestAction($this->createServerRequest(
            'POST',
            'https://example.com/notify.php?token=theToken',
            [
                'REMOTE_ADDR' => '87.65.43.21',
            ],
        )
            ->withQueryParams([
                'token' => 'theToken',
            ])
            ->withParsedBody([
                'status' => 'captured',
            ])
            ->withHeader('User-Agent', 'theUserAgent'));

        $action->execute($request = new GetHttpRequest());

        $this->assertSame('POST', $request->method);
        $this->assertSame('https://example.com/notify.php?token=theToken', $request->uri);
        $this->assertSame([
            'token' => 'theToken',
        ], $request->query);
        $this->assertSame('87.65.43.21', $request->clientIp);
        $this->assertSame('theUserAgent', $request->userAgent);
        $this->assertSame([
            'theUserAgent',
        ], $request->headers['User-Agent']);
    }

    public function testShouldMergeQueryAndParsedBodyTheWayRequestSuperGlobalDoes(): void
    {
        $action = new GetHttpRequestAction($this->createServerRequest()
            ->withQueryParams([
                'fromQuery' => 'aVal',
                'onBoth' => 'theQueryVal',
            ])
            ->withParsedBody([
                'fromBody' => 'anotherVal',
                'onBoth' => 'theBodyVal',
            ]));

        $action->execute($request = new GetHttpRequest());

        $this->assertSame([
            'fromQuery' => 'aVal',
            'onBoth' => 'theBodyVal',
            'fromBody' => 'anotherVal',
        ], $request->request);
    }

    public function testShouldLeaveTheRequestArrayToTheQueryWhenThereIsNoParsedBody(): void
    {
        $action = new GetHttpRequestAction($this->createServerRequest()->withQueryParams([
            'fromQuery' => 'aVal',
        ]));

        $action->execute($request = new GetHttpRequest());

        $this->assertSame([
            'fromQuery' => 'aVal',
        ], $request->request);
    }

    public function testShouldReadTheRawBodyEvenWhenItWasAlreadyRead(): void
    {
        $serverRequest = $this->createServerRequest()
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream('{"id":"theId"}'));

        $serverRequest->getBody()->getContents();

        $action = new GetHttpRequestAction($serverRequest);

        $action->execute($request = new GetHttpRequest());

        $this->assertSame('{"id":"theId"}', $request->content);
    }

    /**
     * @param array<string, mixed> $serverParams
     */
    private function createServerRequest(string $method = 'GET', string $uri = 'https://example.com/', array $serverParams = []): ServerRequestInterface
    {
        return Psr17FactoryDiscovery::findServerRequestFactory()->createServerRequest($method, $uri, $serverParams);
    }
}
