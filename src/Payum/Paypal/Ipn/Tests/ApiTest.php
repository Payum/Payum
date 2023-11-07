<?php

namespace Payum\Paypal\Ipn\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\Ipn\Api;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ApiTest extends TestCase
{
    public function testThrowIfSandboxOptionNotSetInConstructor()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testShouldReturnSandboxIpnEndpointIfSandboxSetTrueInConstructor()
    {
        $api = new Api([
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame('https://www.sandbox.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    public function testShouldReturnLiveIpnEndpointIfSandboxSetFalseInConstructor()
    {
        $api = new Api([
            'sandbox' => false,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame('https://www.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    public function testThrowIfResponseStatusNotOk()
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Client error response');
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(404);
            })
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $api->notifyValidate([]);
    }

    public function testShouldProxyWholeNotificationToClientSend()
    {
        /** @var RequestInterface $actualRequest */
        $actualRequest = null;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use (&$actualRequest) {
                $actualRequest = $request;

                return new Response(200);
            })
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $expectedNotification = [
            'foo' => 'foo',
            'bar' => 'baz',
        ];

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

    public function testShouldReturnVerifiedIfResponseContentVerified()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, [], Api::NOTIFY_VERIFIED);
            })
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $this->assertSame(Api::NOTIFY_VERIFIED, $api->notifyValidate([]));
    }

    public function testShouldReturnInvalidIfResponseContentInvalid()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, [], Api::NOTIFY_INVALID);
            })
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $this->assertSame(Api::NOTIFY_INVALID, $api->notifyValidate([]));
    }

    public function testShouldReturnInvalidIfResponseContentContainsSomethingNotEqualToVerified()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, [], 'foobarbaz');
            })
        ;

        $api = new Api([
            'sandbox' => false,
        ], $clientMock, $this->createHttpMessageFactory());

        $this->assertSame(Api::NOTIFY_INVALID, $api->notifyValidate([]));
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock(HttpClientInterface::class);
    }

    /**
     * @return MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200);
            })
        ;

        return $clientMock;
    }
}
