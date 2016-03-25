<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
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
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
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
     *
     * @expectedException \Payum\Core\Exception\RuntimeException
     * @expectedExceptionMessage The return_url must be set either to FormRequest or to options.
     */
    public function throwIfReturnUrlNeitherSetToFormRequestNorToOptions()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $api->setExpressCheckout(array());
    }

    /**
     * @test
     */
    public function shouldUseReturnUrlSetInFormRequest()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array('RETURNURL' => 'formRequestReturnUrl'));

        $this->assertEquals('formRequestReturnUrl', $result['RETURNURL']);
    }

    /**
     * @test
     */
    public function shouldUseReturnUrlSetInOptions()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array());

        $this->assertEquals('optionReturnUrl', $result['RETURNURL']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RuntimeException
     * @expectedExceptionMessage The return_url must be set either to FormRequest or to options.
     */
    public function throwIfCancelUrlNeitherSetToFormRequestNorToOptions()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $api->setExpressCheckout(array());
    }

    /**
     * @test
     */
    public function shouldUseCancelUrlSetInFormRequest()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array('CANCELURL' => 'formRequestCancelUrl'));

        $this->assertEquals('formRequestCancelUrl', $result['CANCELURL']);
    }

    /**
     * @test
     */
    public function shouldUseCancelUrlSetInOptions()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array());

        $this->assertEquals('optionCancelUrl', $result['CANCELURL']);
    }

    /**
     * @test
     */
    public function shouldAddMethodOnSetExpressCheckoutCall()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array());

        $this->assertArrayHasKey('METHOD', $result);
        $this->assertEquals('SetExpressCheckout', $result['METHOD']);
    }

    /**
     * @test
     */
    public function shouldAddAuthorizeFieldsOnSetExpressCheckoutCall()
    {
        $api = new Api(array(
            'username' => 'the_username',
            'password' => 'the_password',
            'signature' => 'the_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array());

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
    public function shouldAddVersionOnSetExpressCheckoutCall()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $this->createSuccessHttpClientStub(), $this->createHttpMessageFactory());

        $result = $api->setExpressCheckout(array());

        $this->assertArrayHasKey('VERSION', $result);
        $this->assertEquals(Api::VERSION, $result['VERSION']);
    }

    /**
     * @test
     */
    public function shouldGetSandboxAuthorizeUrlIfSandboxTrue()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetAuthorizeUrlWithCustomUserAction()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'useraction' => 'aCustomUserAction',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?useraction=aCustomUserAction&cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetAuthorizeUrlWithCustomUserActionPassedAsQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'useraction' => 'notUsedUseraction',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?useraction=theUseraction&cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken', array('useraction' => 'theUseraction'))
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetAuthorizeUrlWithCustomCmd()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'cmd' => 'theCmd',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=theCmd&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetAuthorizeUrlWithCustomCmdPassedAsQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'cmd' => 'thisCmdNotUsed',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=theCmd&token=theToken',
            $api->getAuthorizeTokenUrl('theToken', array('cmd' => 'theCmd'))
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetAuthorizeUrlWithCustomQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken&foo=fooVal',
            $api->getAuthorizeTokenUrl('theToken', array('foo' => 'fooVal'))
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetAuthorizeUrlWithIgnoredEmptyCustomQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken', array('foo' => ''))
        );
    }

    /**
     * @test
     */
    public function shouldGetRealAuthorizeUrlIfSandboxFalse()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertEquals(
            'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
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
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $clientMock, $this->createHttpMessageFactory());

        $api->setExpressCheckout(array());
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
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $clientMock, $this->createHttpMessageFactory());

        $api->setExpressCheckout(array());
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
