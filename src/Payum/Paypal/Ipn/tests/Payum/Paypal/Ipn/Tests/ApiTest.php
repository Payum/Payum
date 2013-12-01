<?php
namespace Payum\Paypal\Ipn\Tests;

use Buzz\Client\ClientInterface;

use Payum\Paypal\Ipn\Api;

class ApiTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function couldBeConstructedWithBuzzClientAndOptions()
    {
        new Api($this->createClientMock(), array(
            'sandbox' => true
        ));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfSandboxOptionNotSetInConstructor()
    {
        new Api($this->createClientMock(), array());
    }

    /**
     * @test
     */
    public function shouldReturnSandboxIpnEndpointIfSandboxSetTrueInConstructor()
    {
        $api = new Api($this->createClientMock(), array(
            'sandbox' => true
        ));

        $this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    /**
     * @test
     */
    public function shouldReturnLiveIpnEndpointIfSandboxSetFalseInConstructor()
    {
        $api = new Api($this->createClientMock(), array(
            'sandbox' => false
        ));

        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr', $api->getIpnEndpoint());
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\Http\HttpException
     * @expectedExceptionMessage Client error response
     */
    public function throwIfResponseStatusNotOk()
    {
        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->isInstanceOf('Buzz\Message\Form\FormRequest'),
                $this->isInstanceOf('Buzz\Message\Response')
            )
            ->will($this->returnCallback(function($request, $response) {
                $response->setHeaders(array('HTTP/1.1 404 Not Found'));
            }))
        ;

        $api = new Api($clientMock, array(
            'sandbox' => false
        ));

        $api->notifyValidate(array());
    }

    /**
     * @test
     */
    public function shouldProxyWholeNotificationToClientSend()
    {
        $actualRequest = null;
            
        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->isInstanceOf('Buzz\Message\Form\FormRequest'),
                $this->isInstanceOf('Buzz\Message\Response')
            )
            ->will($this->returnCallback(function($request, $response) use (&$actualRequest) {
                $response->setHeaders(array('HTTP/1.1 200 OK'));
                $response->setContent('ACK=Success');
                        
                $actualRequest = $request;
            }))
        ;
        
        $api = new Api($clientMock, array(
            'sandbox' => false
        ));

        $expectedNotification = array(
            'foo' => 'foo',
            'bar' => 'baz'
        );

        $api->notifyValidate($expectedNotification);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        $this->assertEquals(
            array('cmd' => Api::CMD_NOTIFY_VALIDATE) + $expectedNotification, 
            $actualRequest->getFields()
        );
        $this->assertEquals($api->getIpnEndpoint(), $actualRequest->getUrl());
        $this->assertEquals('POST', $actualRequest->getMethod());
    }

    /**
     * @test
     */
    public function shouldReturnVerifiedIfResponseContentVerified()
    {
        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->isInstanceOf('Buzz\Message\Form\FormRequest'),
                $this->isInstanceOf('Buzz\Message\Response')
            )
            ->will($this->returnCallback(function($request, $response) {
                $response->setHeaders(array('HTTP/1.1 200 OK'));
                $response->setContent(Api::NOTIFY_VERIFIED);
            }))
        ;

        $api = new Api($clientMock, array(
            'sandbox' => false
        ));

        $this->assertEquals(Api::NOTIFY_VERIFIED, $api->notifyValidate(array()));
    }

    /**
     * @test
     */
    public function shouldReturnInvalidIfResponseContentInvalid()
    {
        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->isInstanceOf('Buzz\Message\Form\FormRequest'),
                $this->isInstanceOf('Buzz\Message\Response')
            )
            ->will($this->returnCallback(function($request, $response) {
                        $response->setHeaders(array('HTTP/1.1 200 OK'));
                        $response->setContent(Api::NOTIFY_INVALID);
                    }))
        ;

        $api = new Api($clientMock, array(
            'sandbox' => false
        ));

        $this->assertEquals(Api::NOTIFY_INVALID, $api->notifyValidate(array()));
    }

    /**
     * @test
     */
    public function shouldReturnInvalidIfResponseContentContainsSomethingNotEqualToVerified()
    {
        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->isInstanceOf('Buzz\Message\Form\FormRequest'),
                $this->isInstanceOf('Buzz\Message\Response')
            )
            ->will($this->returnCallback(function($request, $response) {
                        $response->setHeaders(array('HTTP/1.1 200 OK'));
                        $response->setContent('foobarbaz');
                    }))
        ;

        $api = new Api($clientMock, array(
            'sandbox' => false
        ));

        $this->assertEquals(Api::NOTIFY_INVALID, $api->notifyValidate(array()));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    protected function createClientMock()
    {
        return $this->getMock('Buzz\Client\ClientInterface', array('send'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Buzz\Client\ClientInterface
     */
    protected function createSuccessClientStub()
    {
        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function($request, $response) {
                $response->setHeaders(array('HTTP/1.1 200 OK'));
            }))
        ;

        return $clientMock;
    }
}