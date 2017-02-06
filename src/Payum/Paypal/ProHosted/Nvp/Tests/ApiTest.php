<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ProHosted\Nvp\Api;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsAndHttpClient()
    {
        $client  = $this->createHttpClientMock();
        $factory = $this->createHttpMessageFactory();

        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => true,
        ), $client, $factory);

        $this->assertAttributeSame($client, 'client', $api);

        $this->assertAttributeSame($factory, 'messageFactory', $api);
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
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     */
    public function shouldUseReturnUrlSetInFormRequest()
    {
        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => true,
            'return'    => 'optionReturnUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCreateButton(array('return' => 'formRequestReturnUrl'));

        $this->assertContains('return=formRequestReturnUrl', $result);
    }

    /**
     * @test
     */
    public function shouldAddAuthorizeFieldsOnDoCreateButtonCall()
    {
        $api = new Api(array(
            'username'  => 'the_username',
            'password'  => 'the_password',
            'signature' => 'the_signature',
            'business'  => 'the_business',
            'subject'   => 'the_business',
            'sandbox'   => true,
            'return'    => 'optionReturnUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCreateButton([]);

        $this->assertArrayHasKey('USER', $result);
        $this->assertEquals('the_username', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertEquals('the_password', $result['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $result);
        $this->assertEquals('the_signature', $result['SIGNATURE']);

        $this->assertArrayHasKey('BUSINESS', $result);
        $this->assertEquals('the_business', $result['BUSINESS']);

        $this->assertArrayHasKey('SUBJECT', $result);
        $this->assertEquals('the_business', $result['SUBJECT']);
    }

    /**
     * @test
     */
    public function shouldAddVersionOnDoCreateButtonCall()
    {
        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => true,
            'return'    => 'optionReturnUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCreateButton([]);

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
        $clientMock->expects($this->once())->method('send')->will($this->returnCallback(function (RequestInterface $request) use (
            $testCase
        ) {
            $testCase->assertEquals('https://api-3t.paypal.com/nvp', $request->getUri());

            return new Response(200, [], $request->getBody());
        }));

        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => false,
            'return'    => 'optionReturnUrl',
        ), $clientMock, $this->createHttpMessageFactory());

        $api->doCreateButton([]);
    }

    /**
     * @test
     */
    public function shouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock->expects($this->once())->method('send')->will($this->returnCallback(function (RequestInterface $request) use (
            $testCase
        ) {
            $testCase->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $request->getUri());

            return new Response(200, [], $request->getBody());
        }));

        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => true,
            'return'    => 'optionReturnUrl',
        ), $clientMock, $this->createHttpMessageFactory());

        $api->doCreateButton([]);
    }

    /**
     * @test
     */
    public function shouldAddMethodOnDoCreateButtonCall()
    {
        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => true,
            'return'    => 'optionReturnUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->doCreateButton([]);

        $this->assertArrayHasKey('METHOD', $result);
        $this->assertEquals('BMCreateButton', $result['METHOD']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RuntimeException
     * @expectedExceptionMessage The return must be set either to FormRequest or to options.
     */
    public function throwIfReturnUrlNeitherSetToFormRequestNorToOptions()
    {
        $api = new Api(array(
            'username'  => 'a_username',
            'password'  => 'a_password',
            'signature' => 'a_signature',
            'business'  => 'a_business',
            'sandbox'   => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $api->doCreateButton([]);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The username, password, signature fields are required.
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMock('Payum\Core\HttpClientInterface');
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
        $clientMock->expects($this->any())->method('send')->will($this->returnCallback(function (RequestInterface $request) {
            return new Response(200, [], $request->getBody());
        }));

        return $clientMock;
    }
}
