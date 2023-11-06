<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The username, password, signature fields are required.');
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testThrowIfSandboxOptionNotSetInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testThrowIfReturnUrlNeitherSetToFormRequestNorToOptions()
    {
        $this->expectException(\Payum\Core\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The return_url must be set either to FormRequest or to options.');
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $api->setExpressCheckout(array());
    }

    public function testShouldUseReturnUrlSetInFormRequest()
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

        $this->assertSame('formRequestReturnUrl', $result['RETURNURL']);
    }

    public function testShouldUseReturnUrlSetInOptions()
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

        $this->assertSame('optionReturnUrl', $result['RETURNURL']);
    }

    public function testThrowIfCancelUrlNeitherSetToFormRequestNorToOptions()
    {
        $this->expectException(\Payum\Core\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The return_url must be set either to FormRequest or to options.');
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $api->setExpressCheckout(array());
    }

    public function testShouldUseCancelUrlSetInFormRequest()
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

        $this->assertSame('formRequestCancelUrl', $result['CANCELURL']);
    }

    public function testShouldUseCancelUrlSetInOptions()
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

        $this->assertSame('optionCancelUrl', $result['CANCELURL']);
    }

    public function testShouldAddMethodOnSetExpressCheckoutCall()
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
        $this->assertSame('SetExpressCheckout', $result['METHOD']);
    }

    public function testShouldAddAuthorizeFieldsOnSetExpressCheckoutCall()
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
        $this->assertSame('the_username', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertSame('the_password', $result['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $result);
        $this->assertSame('the_signature', $result['SIGNATURE']);
    }

    public function testShouldAddVersionOnSetExpressCheckoutCall()
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
        $this->assertSame(Api::VERSION, $result['VERSION']);
    }

    public function testShouldGetSandboxAuthorizeUrlIfSandboxTrue()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    public function testShouldAllowGetAuthorizeUrlWithCustomUserAction()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'useraction' => 'aCustomUserAction',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?useraction=aCustomUserAction&cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    public function testShouldAllowGetAuthorizeUrlWithCustomUserActionPassedAsQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'useraction' => 'notUsedUseraction',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?useraction=theUseraction&cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken', array('useraction' => 'theUseraction'))
        );
    }

    public function testShouldAllowGetAuthorizeUrlWithCustomCmd()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'cmd' => 'theCmd',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=theCmd&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    public function testShouldAllowGetAuthorizeUrlWithCustomCmdPassedAsQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'cmd' => 'thisCmdNotUsed',
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=theCmd&token=theToken',
            $api->getAuthorizeTokenUrl('theToken', array('cmd' => 'theCmd'))
        );
    }

    public function testShouldAllowGetAuthorizeUrlWithCustomQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken&foo=fooVal',
            $api->getAuthorizeTokenUrl('theToken', array('foo' => 'fooVal'))
        );
    }

    public function testShouldAllowGetAuthorizeUrlWithIgnoredEmptyCustomQueryParameter()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken', array('foo' => ''))
        );
    }

    public function testShouldGetRealAuthorizeUrlIfSandboxFalse()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertSame(
            'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken',
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    public function testShouldUseRealApiEndpointIfSandboxFalse()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://api-3t.paypal.com/nvp', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
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

    public function testShouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertSame('https://api-3t.sandbox.paypal.com/nvp', (string) $request->getUri());

                return new Response(200, [], $request->getBody());
            })
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
        return $this->createMock('Payum\Core\HttpClientInterface');
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
            ->method('send')
            ->willReturnCallback(function (RequestInterface $request) {
                return new Response(200, [], $request->getBody());
            })
        ;

        return $clientMock;
    }
}
