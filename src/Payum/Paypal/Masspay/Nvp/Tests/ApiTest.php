<?php
namespace Payum\Paypal\Masspay\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\Masspay\Nvp\Api;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsAndHttpClient()
    {
        $client = $this->createHttpClientMock();
        $factory = $this->createHttpMessageFactory();

        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $client, $factory);

        $this->assertAttributeSame($client, 'client', $api);
        $this->assertAttributeSame($factory, 'messageFactory', $api);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The username, password, signature fields are required.
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfSandboxOptionNotSetInConstructor()
    {
        new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     */
    public function shouldAddMethodOnMasspayCall()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('METHOD', $result);
        $this->assertEquals('MassPay', $result['METHOD']);
    }

    /**
     * @test
     */
    public function shouldAddAuthorizeFieldsOnMasspayCall()
    {
        $api = new Api(array(
            'username' => 'the_username',
            'password' => 'the_password',
            'signature' => 'the_signature',
            'sandbox' => true,
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('USER', $result);
        $this->assertEquals('the_username', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertEquals('the_password', $result['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $result);
        $this->assertEquals('the_signature', $result['SIGNATURE']);
    }

    /**
     * @test
     */
    public function shouldAddVersionOnMasspayCall()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->massPay([]);

        $this->assertArrayHasKey('VERSION', $result);
        $this->assertEquals(Api::VERSION, $result['VERSION']);
    }

    /**
     * @test
     */
    public function shouldUseRealApiEndpointIfSandboxFalse()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertEquals('https://api-3t.paypal.com/nvp', $request->getUri());

                return new Response(200, [], $request->getBody());
            }))
        ;

        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->massPay([]);
    }

    /**
     * @test
     */
    public function shouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $request->getUri());

                return new Response(200, [], $request->getBody());
            }))
        ;

        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $clientMock, $this->createHttpMessageFactory());

        $api->massPay([]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMock(HttpClientInterface::class);
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
                return new Response(200, [], $request->getBody());
            }))
        ;

        return $clientMock;
    }
}
