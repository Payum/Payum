<?php
namespace Payum\Tests\Functional\Paypal\ExpressCheckout\Nvp;

use Buzz\Client\Curl;
use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Api;

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
        
        $this->api = new Api(
            new Curl,
            $GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_USERNAME'],
            $GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_PASSWORD'],
            $GLOBALS['__PAYUM_PAYPAL_EXPRESS_CHECKOUT_NVP_API_SIGNATURE'],
            'http://localhost/returnUrl',
            'http://localhost/cancelUrl',
            $debug = true
        );
    }
    
    public function testFoo()
    {
        $request = new FormRequest();
        $request->setField('PAYMENTREQUEST_0_AMT', 1);
        $request->setField('FOO', "BAR");

        var_dump($nvp = $this->api->setExpressCheckout($request)->getNvp());
        var_dump($this->api->getAuthorizeTokenUrl($nvp['TOKEN']));

        $request = new FormRequest();
        $request->setField('TOKEN', $nvp['TOKEN']);

        var_dump($this->api->getExpressCheckoutDetails($request)->parseNvp());
    }

    public function testBar()
    {
        $request = new FormRequest();
        $request->setField('TOKEN', 'EC-9L903951P4857435X');
        
        var_dump($this->api->getExpressCheckoutDetails($request)->parseNvp());
    }
    
    public function testOlolo()
    {
        $request = new FormRequest();
        $request->setField('TOKEN', 'EC-5FR17426FC745224V');
        $request->setField('PAYMENTREQUEST_0_AMT', 1);
        $request->setField('PAYMENTREQUEST_0_PAYMENTACTION', 'Sale');
        $request->setField('PAYERID', 'FC9Q7XRNAXJZG');
        
        var_dump($this->api->doExpressCheckoutPayment($request)->parseNvp());
    }
}