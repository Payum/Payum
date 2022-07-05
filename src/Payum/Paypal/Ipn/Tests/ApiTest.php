<?php

namespace Payum\Paypal\Ipn\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\Ipn\Api;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowIfSandboxOptionNotSetInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testShouldReturnSandboxIpnEndpointIfSandboxSetTrueInConstructor()
    {
        $api = new Api(array(
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame('https://www.sandbox.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    public function testShouldReturnLiveIpnEndpointIfSandboxSetFalseInConstructor()
    {
        $api = new Api(array(
            'sandbox' => false,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame('https://www.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    public function testThrowIfResponseStatusNotOk()
    {
        $this->expectException(\Payum\Core\Exception\Http\HttpException::class);
        $this->expectExceptionMessage('Client error response');
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(404);
            })
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->notifyValidate(array());
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

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $expectedNotification = array(
            'foo' => 'foo',
            'bar' => 'baz',
        );

        $api->notifyValidate($expectedNotification);

        $content = array();
        parse_str($actualRequest->getBody()->getContents(), $content);

        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $actualRequest);
        $this->assertSame($expectedNotification + array('cmd' => Api::CMD_NOTIFY_VALIDATE), $content);
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
                return new Response(200, array(), Api::NOTIFY_VERIFIED);
            })
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $this->assertSame(Api::NOTIFY_VERIFIED, $api->notifyValidate(array()));
    }

    public function testShouldReturnInvalidIfResponseContentInvalid()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, array(), Api::NOTIFY_INVALID);
            })
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $this->assertSame(Api::NOTIFY_INVALID, $api->notifyValidate(array()));
    }

    public function testShouldReturnInvalidIfResponseContentContainsSomethingNotEqualToVerified()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, array(), 'foobarbaz');
            })
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $this->assertSame(Api::NOTIFY_INVALID, $api->notifyValidate(array()));
    }

    /**
     * @return MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock('Payum\Core\HttpClientInterface', array('send'));
    }

    /**
     * @return \Http\Message\MessageFactory
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
