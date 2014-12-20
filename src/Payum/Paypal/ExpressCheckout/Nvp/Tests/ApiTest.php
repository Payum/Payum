<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use Buzz\Message\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Buzz\Message\Form\FormRequest;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsOnly()
    {
        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ));

        $this->assertAttributeInstanceOf('Buzz\Client\Curl', 'client', $api);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithOptionsAndBuzzClient()
    {
        $client = $this->createClientMock();

        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ), $client);

        $this->assertAttributeSame($client, 'client', $api);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The username option must be set.
     */
    public function throwIfUsernameOptionNotSetInConstructor()
    {
        new Api(array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The password option must be set.
     */
    public function throwIfPasswordOptionNotSetInConstructor()
    {
        new Api(array(
            'username' => 'a_username',
        ));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The signature option must be set.
     */
    public function throwIfSignatureOptionNotSetInConstructor()
    {
        new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
        ));
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
        ));
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
        ));

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
        ), $this->createSuccessClientStub());

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
        ), $this->createSuccessClientStub());

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
        ), $this->createClientMock());

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
        ), $this->createSuccessClientStub());

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
        ), $this->createSuccessClientStub());

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
        ), $this->createSuccessClientStub());

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
        ), $this->createSuccessClientStub());

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
        ), $this->createSuccessClientStub());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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

        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (FormRequest $request, Response $response) use ($testCase) {
                $testCase->assertEquals('https://api-3t.paypal.com/nvp', $request->getUrl());

                $response->setHeaders(array('HTTP/1.1 200 OK'));
                $response->setContent('ACK=Success');

                $response->setContent(http_build_query($request->getFields()));
            }))
        ;

        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $clientMock);

        $api->setExpressCheckout(array());
    }

    /**
     * @test
     */
    public function shouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (FormRequest $request, Response $response) use ($testCase) {
                $testCase->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $request->getUrl());

                $response->setHeaders(array('HTTP/1.1 200 OK'));
                $response->setContent('ACK=Success');

                $response->setContent(http_build_query($request->getFields()));
            }))
        ;

        $api = new Api(array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl',
        ), $clientMock);

        $api->setExpressCheckout(array());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Buzz\Client\ClientInterface
     */
    protected function createClientMock()
    {
        return $this->getMock('Buzz\Client\ClientInterface');
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
            ->will($this->returnCallback(function (FormRequest $request, Response $response) {
                $response->setHeaders(array('HTTP/1.1 200 OK'));
                $response->setContent('ACK=Success');

                $response->setContent(http_build_query($request->getFields()));
            }))
        ;

        return $clientMock;
    }
}
