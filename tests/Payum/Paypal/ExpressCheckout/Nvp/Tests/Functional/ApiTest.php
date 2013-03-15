<?php
namespace Payum\Functional\Paypal\ExpressCheckout\Nvp\Tests\Functional;

use Buzz\Client\Curl;
use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected $api;
    
    public function setUp()
    {
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_ACCOUNT'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_ACCOUNT in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_USERNAME'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_USERNAME in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_PASSWORD'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_PASSWORD in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_SIGNATURE'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_SIGNATURE in your phpunit.xml');
        }
        if (false == extension_loaded('curl')) {
            $this->markTestSkipped('Curl extension is required to run this tests.');
        }
        
        $this->api = new Api(new Curl, array(
            'username' => $GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_USERNAME'],
            'password' => $GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_PASSWORD'],
            'signature' => $GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_SIGNATURE'],
            'return_url' => 'http://localhost/returnUrl',
            'cancel_url' => 'http://localhost/cancelUrl',
            'sandbox' => true
        ));
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCallSetExpressCheckout()
    {
        $request = new FormRequest();
        $request->setField('PAYMENTREQUEST_0_AMT', 1);

        $response = $this->api->setExpressCheckout($request);
    
        $this->assertEquals(Api::ACK_SUCCESS, $response['ACK']);
        $this->assertNotEmpty($response['TOKEN']);
        $this->assertNotEmpty($response['TIMESTAMP']);
        $this->assertNotEmpty($response['CORRELATIONID']);
        $this->assertNotEmpty($response['VERSION']);
        $this->assertNotEmpty($response['BUILD']);
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCallGetExpressCheckoutDetails()
    {
        $request = new FormRequest();
        $request->setField('PAYMENTREQUEST_0_AMT', 1);

        $setExpressCheckoutResponse = $this->api->setExpressCheckout($request);

        //gurad
        $this->assertEquals(Api::ACK_SUCCESS, $setExpressCheckoutResponse['ACK']);

        $request = new FormRequest();
        $request->setField('TOKEN', $setExpressCheckoutResponse['TOKEN']);

        $getExpressCheckoutDetailsResponse = $this->api->getExpressCheckoutDetails($request);

        $this->assertEquals(Api::ACK_SUCCESS, $getExpressCheckoutDetailsResponse['ACK']);
        $this->assertEquals(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED, $getExpressCheckoutDetailsResponse['CHECKOUTSTATUS']);
        $this->assertEquals($setExpressCheckoutResponse['TOKEN'], $getExpressCheckoutDetailsResponse['TOKEN']);
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCallDoExpressCheckoutPayment()
    {
        $request = new FormRequest();
        $request->setField('PAYMENTREQUEST_0_AMT', 1);

        $setExpressCheckoutResponse = $this->api->setExpressCheckout($request);

        //gurad
        $this->assertEquals(Api::ACK_SUCCESS, $setExpressCheckoutResponse['ACK']);
        
        //we cannot test success scenario of this request. So at least we can test the failure one.  
        try {
            $request = new FormRequest();
            $request->setField('TOKEN', $setExpressCheckoutResponse['TOKEN']);
            
            $this->api->doExpressCheckoutPayment($request);
        } catch (HttpResponseAckNotSuccessException $e) {
            $response = $e->getResponse();
            
            $this->assertEquals(Api::ACK_FAILURE, $response['ACK']);
            $this->assertEquals('Order total is missing.', $response['L_LONGMESSAGE0']);
            $this->assertEquals('The PayerID value is invalid.', $response['L_LONGMESSAGE1']);
            
            return;
        }
        
        $this->fail('Expected `HttpResponseAckNotSuccessException` exception.');
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCallGetTransactionDetails()
    {
        //we cannot test success scenario of this request. So at least we can test the failure one.  
        try {
            $request = new FormRequest();
            $request->setField('TRANSACTIONID', 'aTransId');

            $this->api->getTransactionDetails($request);
        } catch (HttpResponseAckNotSuccessException $e) {
            $response = $e->getResponse();

            $this->assertEquals(Api::ACK_FAILURE, $response['ACK']);
            $this->assertEquals('The transaction id is not valid', $response['L_LONGMESSAGE0']);

            return;
        }

        $this->fail('Expected `HttpResponseAckNotSuccessException` exception.');
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCreateRecurringPaymentsProfile()
    {
        //we cannot test success scenario of this request. So at least we can test the failure one.  
        try {
            $this->api->createRecurringPaymentsProfile(new FormRequest());
        } catch (HttpResponseAckNotSuccessException $e) {
            $response = $e->getResponse();

            $this->assertEquals(Api::ACK_FAILURE, $response['ACK']);
            $this->assertEquals('Missing Token or buyer credit card', $response['L_LONGMESSAGE0']);

            return;
        }

        $this->fail('Expected `HttpResponseAckNotSuccessException` exception.');
    }

    /**
     * @test
     */
    public function shouldSuccessfullyCreateRecurringPaymentsProfileWithToken()
    {
        //we cannot test success scenario of this request. So at least we can test the failure one.

        $request = new FormRequest();
        $request->setField('PAYMENTREQUEST_0_AMT', 1);

        $setExpressCheckoutResponse = $this->api->setExpressCheckout($request);

        //gurad
        $this->assertEquals(Api::ACK_SUCCESS, $setExpressCheckoutResponse['ACK']);        
        
        try {
            $request = new FormRequest();
            $request->setField('TOKEN', $setExpressCheckoutResponse['TOKEN']);
            
            $this->api->createRecurringPaymentsProfile($request);
        } catch (HttpResponseAckNotSuccessException $e) {
            $response = $e->getResponse();

            $this->assertEquals(Api::ACK_FAILURE, $response['ACK']);
            $this->assertEquals('Billing period must be one of Day, Week, SemiMonth, or Year', $response['L_LONGMESSAGE0']);
            $this->assertEquals('Billing frequency must be > 0 and be less than or equal to one year', $response['L_LONGMESSAGE1']);

            return;
        }

        $this->fail('Expected `HttpResponseAckNotSuccessException` exception.');
    }
}