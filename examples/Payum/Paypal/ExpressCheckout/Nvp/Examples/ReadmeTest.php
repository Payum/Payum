<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples;

use Buzz\Client\Curl;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Action\CaptureAwesomeCartAction;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function doCapture()
    {
        //@testo:start
        $payment = PaymentFactory::create(new Api(new Curl, array(
            'username' => 'a_username',
            'password' => 'a_pasword',
            'signature' => 'a_signature',
            'sandbox' => true
        )));

        $capture = new CaptureRequest(array(
            'PAYMENTREQUEST_0_AMT' => 10,
            'PAYMENTREQUEST_0_CURRENCY' => 'USD',
            'RETURNURL' => 'http://foo.com/finishPayment',
            'CANCELURL' => 'http://foo.com/finishPayment',
        ));
        
        if ($interactiveRequest = $payment->execute($capture, $expectsInteractive = true)) {
            //save your models somewhere.
            if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
                header('Location: '.$interactiveRequest->getUrl());
                exit;
            }
            
            throw $interactiveRequest;
        }
        // ...
        //@testo:end
        
        $this->assertArrayHasKey('L_LONGMESSAGE0', $capture->getModel());
        $this->assertEquals('Security header is not valid', $capture->getModel()['L_LONGMESSAGE0']);
        
        return array(
            $payment,
            $capture
        );
    }

    /**
     * @test
     */
    public function doDigitalGoodsCapture()
    {
        $whatever ='doo';
        //@testo:start
        // ...
        
        $capture = new CaptureRequest(array(
            
            // ... 
            
            'NOSHIPPING' => Api::NOSHIPPING_NOT_DISPLAY_ADDRESS,
            'REQCONFIRMSHIPPING' => Api::REQCONFIRMSHIPPING_NOT_REQUIRED,
            'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => Api::PAYMENTREQUEST_ITERMCATEGORY_DIGITAL, 
            'L_PAYMENTREQUEST_0_NAME0' => 'Awesome e-book',
            'L_PAYMENTREQUEST_0_DESC0' => 'Great stories of America.',
            'L_PAYMENTREQUEST_0_AMT0' => 10,
            'L_PAYMENTREQUEST_0_QTY0' => 1,
            'L_PAYMENTREQUEST_0_TAXAMT0' => 2,
        ));

        // ...
        //@testo:end
    }

    /**
     * @test
     *
     * @depends doCapture
     */
    public function doStatus(array $arguments)
    {
        $payment = $arguments[0];
        $capture = $arguments[1];
        
        unset($capture->getModel()['L_ERRORCODE0']);
        $capture->getModel()['CHECKOUTSTATUS'] = Api::CHECKOUTSTATUS_PAYMENT_COMPLETED;
        $capture->getModel()['PAYMENTREQUEST_0_PAYMENTSTATUS'] = Api::PAYMENTSTATUS_COMPLETED;
        
        //@testo:start
        //...
        
        $status = new BinaryMaskStatusRequest($capture->getModel());
        $payment->execute($status);
        
        if ($status->isSuccess()) {
            //@testo:end
            if (false) {
            //@testo:start
            echo 'We are done';
            //@testo:end
            }
            //@testo:start
        }

        //@testo:end
        if (false) {
        //@testo:start
        echo "Hmm. We are not. Let's check other possible statuses!";
        //@testo:end
        }

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     *
     * @depends doCapture
     */
    public function doCaptureAwesomeCart(array $arguments)
    {
        $payment = $arguments[0];

        //@testo:start
        //...
        $cart = new AwesomeCart;

        $payment->addAction(new CaptureAwesomeCartAction);

        $capture = new CaptureRequest($cart);
        if ($interactiveRequest = $payment->execute($capture, $expectsInteractive = true)) {
            if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
                header('Location: '.$interactiveRequest->getUrl());
                exit;
            }

            throw $interactiveRequest; //unexpected request
        }

        $status = new BinaryMaskStatusRequest($capture->getModel()->getPaymentDetails());
        $payment->execute($status);

        if ($status->isSuccess()) {
            //@testo:end
            if (false) {
                //@testo:start
            echo 'We are done';
                //@testo:end
            }
            //@testo:start
        }

        //@testo:end
        if (false) {
            //@testo:start
        echo "Hmm. We are not. Let's check other possible statuses!";
            //@testo:end
        }

        $this->assertTrue($status->isFailed());
        
        $paymentDetails = $status->getModel();
        $this->assertEquals('Security header is not valid', $paymentDetails['L_LONGMESSAGE0']);
    }
}