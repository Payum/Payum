<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Buzz\Message\Form\FormRequest;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithBuzzClientAndOptions()
    {
        new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The username option must be set.
     */
    public function throwIfUsernameOptionNotSetInConstructor()
    {
        new Api($this->createClientMock(), array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The password option must be set.
     */
    public function throwIfPasswordOptionNotSetInConstructor()
    {
        new Api($this->createClientMock(), array(
            'username' => 'a_username'
        ));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage The signature option must be set.
     */
    public function throwIfSignatureOptionNotSetInConstructor()
    {
        new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password'
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
        new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
        ));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RuntimeException
     * @expectedExceptionMessage The return_url must be set either to FormRequest or to options.
     */
    public function throwIfReturnUrlNeitherSetToFormRequestNorToOptions()
    {
        $api = new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ));
        
        $api->setExpressCheckout(new FormRequest);
    }

    /**
     * @test
     */
    public function shouldUseReturnUrlSetInFormRequest()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));
        
        $request = new FormRequest;
        $request->setField('RETURNURL', 'formRequestReturnUrl');

        $api->setExpressCheckout($request);
        
        $fields = $request->getFields();
        $this->assertEquals('formRequestReturnUrl', $fields['RETURNURL']);
    }

    /**
     * @test
     */
    public function shouldUseReturnUrlSetInOptions()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $fields = $request->getFields();
        $this->assertEquals('optionReturnUrl', $fields['RETURNURL']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RuntimeException
     * @expectedExceptionMessage The return_url must be set either to FormRequest or to options.
     */
    public function throwIfCancelUrlNeitherSetToFormRequestNorToOptions()
    {
        $api = new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ));

        $api->setExpressCheckout(new FormRequest);
    }

    /**
     * @test
     */
    public function shouldUseCancelUrlSetInFormRequest()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;
        $request->setField('CANCELURL', 'formRequestCancelUrl');

        $api->setExpressCheckout($request);

        $fields = $request->getFields();
        $this->assertEquals('formRequestCancelUrl', $fields['CANCELURL']);
    }

    /**
     * @test
     */
    public function shouldUseCancelUrlSetInOptions()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $fields = $request->getFields();
        $this->assertEquals('optionCancelUrl', $fields['CANCELURL']);
    }

    /**
     * @test
     */
    public function shouldAddMethodOnSetExpressCheckoutCall()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $fields = $request->getFields();
        
        $this->assertArrayHasKey('METHOD', $fields);
        $this->assertEquals('SetExpressCheckout', $fields['METHOD']);
    }

    /**
     * @test
     */
    public function shouldAddAuthorizeFieldsOnSetExpressCheckoutCall()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'the_username',
            'password' => 'the_password',
            'signature' => 'the_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $fields = $request->getFields();
        
        $this->assertArrayHasKey('USER', $fields);
        $this->assertEquals('the_username', $fields['USER']);

        $this->assertArrayHasKey('PWD', $fields);
        $this->assertEquals('the_password', $fields['PWD']);

        $this->assertArrayHasKey('SIGNATURE', $fields);
        $this->assertEquals('the_signature', $fields['SIGNATURE']);
    }

    /**
     * @test
     */
    public function shouldAddVersionOnSetExpressCheckoutCall()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $fields = $request->getFields();
        
        $this->assertArrayHasKey('VERSION', $fields);
        $this->assertEquals(Api::VERSION, $fields['VERSION']);
    }

    /**
     * @test
     */
    public function shouldGetSandboxAuthorizeUrlIfSandboxTrue()
    {
        $api = new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
        ));
        
        $this->assertEquals(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=theToken', 
            $api->getAuthorizeTokenUrl('theToken')
        );
    }

    /**
     * @test
     */
    public function shouldGetRealAuthorizeUrlIfSandboxFalse()
    {
        $api = new Api($this->createClientMock(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
        ));

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
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => false,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $this->assertEquals('https://api-3t.paypal.com/nvp', $request->getUrl());
    }

    /**
     * @test
     */
    public function shouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $api = new Api($this->createSuccessClientStub(), array(
            'username' => 'a_username',
            'password' => 'a_password',
            'signature' => 'a_signature',
            'sandbox' => true,
            'return_url' => 'optionReturnUrl',
            'cancel_url' => 'optionCancelUrl'
        ));

        $request = new FormRequest;

        $api->setExpressCheckout($request);

        $this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', $request->getUrl());
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
            ->will($this->returnCallback(function($request, $response) {
                $response->setHeaders(array('HTTP/1.1 200 OK'));
                $response->setContent('ACK=Success');
            }))
        ;
        
        return $clientMock;
    }
}
