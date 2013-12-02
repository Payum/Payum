<?php
namespace Payum\Paypal\Ipn\Tests\Functional;

use Buzz\Client\Curl;
use Payum\Paypal\Ipn\Api;

class ApiTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @var Api
     */
    protected $api;
    
    protected $notification;
    
    public function setUp()
    {
        $curl = new Curl;
        $curl->setVerifyPeer(false);

        $this->api = new Api($curl, array(
            'sandbox' => true,
        ));
        
        $this->notification = array (
            'test_ipn' => '1',
            'payment_type' => 'echeck',
            'payment_date' => '01:05:22 Apr 27, 2012 PDT',
            'payment_status' => 'Completed',
            'address_status' => 'confirmed',
            'payer_status' => 'verified',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'payer_email' => 'buyer@paypalsandbox.com',
            'payer_id' => 'TESTBUYERID01',
            'address_name' => 'John Smith',
            'address_country' => 'United States',
            'address_country_code' => 'US',
            'address_zip' => '95131',
            'address_state' => 'CA',
            'address_city' => 'San Jose',
            'address_street' => '123, any street',
            'business' => 'seller@paypalsandbox.com',
            'receiver_email' => 'seller@paypalsandbox.com',
            'receiver_id' => 'TESTSELLERID1',
            'residence_country' => 'US',
            'item_name' => 'something',
            'item_number' => 'AK-1234',
            'quantity' => '1',
            'shipping' => '3.04',
            'tax' => '2.02',
            'mc_currency' => 'USD',
            'mc_fee' => '0.44',
            'mc_gross' => '12.34',
            'txn_type' => 'web_accept',
            'txn_id' => '2242785',
            'notify_version' => '2.1',
            'custom' => 'xyz123',
            'invoice' => 'abc1234',
            'charset' => 'windows-1252',
            'verify_sign' => 'ANynA0jMWQcMYG.j0mGa9lL.YtA6AEIMNrCW4IiWOlSOJ-B2Rn3qij4z'
        );
    }
    
    /**
     * @test
     */
    public function shouldReturnVerifiedStatusIfValidNotificationSent()
    {
        $this->assertEquals(
            Api::NOTIFY_VERIFIED, 
            $this->api->notifyValidate($this->notification)
        );
    }

    /**
     * @test
     */
    public function shouldReturnInvalidStatusIfInvalidNotificationSent()
    {
        $this->assertEquals(
            Api::NOTIFY_INVALID, 
            $this->api->notifyValidate(array())
        );
    }
}