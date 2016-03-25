<?php
namespace Payum\Paypal\Ipn\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\Ipn\Api;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithHttpClientAndOptions()
    {
        new Api(array(
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfSandboxOptionNotSetInConstructor()
    {
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     */
    public function shouldReturnSandboxIpnEndpointIfSandboxSetTrueInConstructor()
    {
        $api = new Api(array(
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    /**
     * @test
     */
    public function shouldReturnLiveIpnEndpointIfSandboxSetFalseInConstructor()
    {
        $api = new Api(array(
            'sandbox' => false,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\Http\HttpException
     * @expectedExceptionMessage Client error response
     */
    public function throwIfResponseStatusNotOk()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(404);
            }))
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->notifyValidate(array());
    }

    /**
     * @test
     */
    public function shouldProxyWholeNotificationToClientSend()
    {
        /** @var RequestInterface $actualRequest */
        $actualRequest = null;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use (&$actualRequest) {
                $actualRequest = $request;

                return new Response(200);
            }))
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
        $this->assertEquals(array('cmd' => Api::CMD_NOTIFY_VALIDATE) + $expectedNotification, $content);
        $this->assertEquals($api->getIpnEndpoint(), $actualRequest->getUri());
        $this->assertEquals('POST', $actualRequest->getMethod());
    }

    /**
     * @test
     */
    public function shouldReturnVerifiedIfResponseContentVerified()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200, array(), Api::NOTIFY_VERIFIED);
            }))
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $this->assertEquals(Api::NOTIFY_VERIFIED, $api->notifyValidate(array()));
    }

    /**
     * @test
     */
    public function shouldReturnInvalidIfResponseContentInvalid()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200, array(), Api::NOTIFY_INVALID);
            }))
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $this->assertEquals(Api::NOTIFY_INVALID, $api->notifyValidate(array()));
    }

    /**
     * @test
     */
    public function shouldReturnInvalidIfResponseContentContainsSomethingNotEqualToVerified()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200, array(), 'foobarbaz');
            }))
        ;

        $api = new Api(array(
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $this->assertEquals(Api::NOTIFY_INVALID, $api->notifyValidate(array()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMock('Payum\Core\HttpClientInterface', array('send'));
    }

    /**
     * @return \Http\Message\MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200);
            }))
        ;

        return $clientMock;
    }
}
