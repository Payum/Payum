<?php
namespace Payum\Paypal\ProCheckout\Nvp\Examples;

use Buzz\Client\Curl;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ProCheckout\Nvp\Model\PaymentDetails;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\CaptureRequest;

class ExecuteTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @test
    */
    public function doCapture()
    {
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_USERNAME'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_USERNAME in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_PASSWORD'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_PASSWORD in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_PARTNER'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_PARTNER in your phpunit.xml');
        }
        if (false == isset($GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_VENDOR'])) {
            $this->markTestSkipped('Please configure __PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_VENDOR in your phpunit.xml');
        }



        //@testo:start
        $client = new Curl;
        $client->setTimeout(20);

        $payment = PaymentFactory::create(new Api($client, array(
            'username' => $GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_USERNAME'],
            'password' => $GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_PASSWORD'],
            'partner' => $GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_PARTNER'],
            'vendor' => $GLOBALS['__PAYUM_PAYPAL_PRO_CHECKOUT_NVP_API_VENDOR'],
            'sandbox' => true
        )));

        $instruction = new PaymentDetails();
        $instruction->setCurrency('USD');
        $instruction->setAmt('1.00');
        $instruction->setAcct('5105105105105100');
        $instruction->setExpDate('1214');
        $instruction->setCvv2('123');
        $instruction->setBillToFirstName('John');
        $instruction->setBillToLastName('Doe');
        $instruction->setBillToStreet('123 Main St.');
        $instruction->setBillToCity('San Jose');
        $instruction->setBillToState('CA');
        $instruction->setBillToZip('95101');
        $instruction->setBillToCountry('US');

        $captureRequest = new CaptureRequest($instruction);
        $payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payment->execute($statusRequest);

        $response = $instruction->getResponse();
        // ...
        //@testo:end
        $this->assertNotEmpty($response);
        $this->assertArrayHasKey('RESULT', $response);
        $this->assertEquals(Api::RESULT_SUCCESS, $response['RESULT']);
    }
}
