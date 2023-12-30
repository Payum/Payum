<?php

namespace Payum\Paypal\Ipn\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use Nyholm\Psr7\Stream;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Paypal\Ipn\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function http_build_query;

class ApiTest extends TestCase
{
    public function testThrowIfSandboxOptionNotSetInConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testShouldReturnSandboxIpnEndpointIfSandboxSetTrueInConstructor(): void
    {
        $api = new Api([
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $this->assertSame('https://www.sandbox.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    public function testShouldReturnLiveIpnEndpointIfSandboxSetFalseInConstructor(): void
    {
        $api = new Api([
            'sandbox' => false,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $this->assertSame('https://www.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    public function testThrowIfResponseStatusNotOk(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Client error response');
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(fn (RequestInterface $request) => Psr17FactoryDiscovery::findResponseFactory()->createResponse(404)->withBody($request->getBody()))
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->notifyValidate([]);
    }

    public function testShouldProxyWholeNotificationToClientSend(): void
    {
        /** @var RequestInterface $actualRequest */
        $actualRequest = null;

        $expectedNotification = [
            'foo' => 'foo',
            'bar' => 'baz',
        ];

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use (&$actualRequest, $expectedNotification) {
                $actualRequest = $request->withBody(Stream::create(http_build_query($expectedNotification + [
                    'cmd' => Api::CMD_NOTIFY_VALIDATE,
                ])));

                return Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody());
            })
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $api->notifyValidate($expectedNotification);

        $content = [];
        parse_str($actualRequest->getBody()->getContents(), $content);

        $this->assertInstanceOf(RequestInterface::class, $actualRequest);
        $this->assertSame($expectedNotification + [
            'cmd' => Api::CMD_NOTIFY_VALIDATE,
        ], $content);
        $this->assertSame($api->getIpnEndpoint(), (string) $actualRequest->getUri());
        $this->assertSame('POST', $actualRequest->getMethod());
    }

    public function testShouldReturnVerifiedIfResponseContentVerified(): void
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(fn (RequestInterface $request) => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody(Stream::create(Api::NOTIFY_VERIFIED)))
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $this->assertSame(Api::NOTIFY_VERIFIED, $api->notifyValidate([]));
    }

    public function testShouldReturnInvalidIfResponseContentInvalid(): void
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(fn (RequestInterface $request) => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody(Stream::create(Api::NOTIFY_INVALID)))
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $this->assertSame(Api::NOTIFY_INVALID, $api->notifyValidate([]));
    }

    public function testShouldReturnInvalidIfResponseContentContainsSomethingNotEqualToVerified(): void
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(fn (RequestInterface $request) => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody(Stream::create('foobarbaz')))
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $this->assertSame(Api::NOTIFY_INVALID, $api->notifyValidate([]));
    }

    protected function createHttpClientMock(): MockObject | ClientInterface
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function createHttpMessageFactory(): RequestFactoryInterface
    {
        return Psr17FactoryDiscovery::findRequestFactory();
    }

    protected function createHttpStreamFactory(): StreamFactoryInterface
    {
        return Psr17FactoryDiscovery::findStreamFactory();
    }

    protected function createSuccessHttpClientStub(): MockObject | ClientInterface
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->method('sendRequest')
            ->willReturnCallback(fn (RequestInterface $request) => Psr17FactoryDiscovery::findResponseFactory()->createResponse(200)->withBody($request->getBody()))
        ;

        return $clientMock;
    }
}
