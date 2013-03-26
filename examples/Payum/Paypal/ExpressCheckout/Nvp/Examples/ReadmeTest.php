<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples;

use Buzz\Client\Curl;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfileRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetailsRequest;
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
        //@testo:source
        //@testo:uncomment:use Buzz\Client\Curl;
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Api;
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
        //@testo:uncomment:use Payum\Request\CaptureRequest;
        //@testo:uncomment:use Payum\Request\RedirectUrlInteractiveRequest;
        
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
        //@testo:source
        
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
        //@testo:source
        //@testo:uncomment:use Payum\Request\BinaryMaskStatusRequest;
        
        $status = new BinaryMaskStatusRequest($capture->getModel());
        $payment->execute($status);
        
        if ($status->isSuccess()) {
            //@testo:uncomment:echo 'We are done';
        }

        //@testo:uncomment:echo "Hmm. We are not. Let's check other possible statuses!";
        //@testo:end
        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function createBillingAgrement()
    {
        //@testo:start
        //@testo:source
        //@testo:uncomment:use Payum\Request\CaptureRequest;
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Api;
        
        $captureBillingAgreement = new CaptureRequest(array(
            'PAYMENTREQUEST_0_AMT' => 0,
            'RETURNURL' => 'http://foo.com/finishPayment',
            'CANCELURL' => 'http://foo.com/finishPayment',
            'L_BILLINGTYPE0' => Api::BILLINGTYPE_RECURRING_PAYMENTS,
            'L_BILLINGAGREEMENTDESCRIPTION0' => 'Subsribe for weather forecast',
        ));

        // ...
        //@testo:end

        $billingAgreementDetails = $captureBillingAgreement->getModel();
        $billingAgreementDetails['TOKEN'] = 'aToken';
        $billingAgreementDetails['EMAIL'] = 'foo@example.com';

        $captureBillingAgreement->setModel($billingAgreementDetails);
        
        return $captureBillingAgreement;
    }

    /**
     * @test
     * 
     * @depends createBillingAgrement
     */
    public function createRecurringPaymnt($captureBillingAgreement)
    {
        $payment = PaymentFactory::create(new Api(new Curl, array(
            'username' => 'a_username',
            'password' => 'a_pasword',
            'signature' => 'a_signature',
            'sandbox' => true
        )));
        
        //@testo:start
        //@testo:source
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Api;
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileRequest;
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsRequest;
        
        $billingAgreementDetails = $captureBillingAgreement->getModel();

        $recurringPaymentDetails = new \ArrayObject(array(
            'TOKEN' => $billingAgreementDetails['TOKEN'],
            'PROFILESTARTDATE' => date(DATE_ATOM),
            'DESC' => $billingAgreementDetails['L_BILLINGAGREEMENTDESCRIPTION0'],
            'AMT' => 1.45,
            'CURRENCYCODE' => 'USD',
            'BILLINGPERIOD' => Api::BILLINGPERIOD_DAY,
            'BILLINGFREQUENCY' => 2,
            'EMAIL' => $billingAgreementDetails['EMAIL'],
        ));
        //@testo:end
        $recurringPaymentDetails['PROFILEID'] = 'aProfileid';
        //@testo:start

        $payment->execute(
            new CreateRecurringPaymentProfileRequest($recurringPaymentDetails)
        );
        $payment->execute(
            new GetRecurringPaymentsProfileDetailsRequest($recurringPaymentDetails)
        );

        $recurringPaymentStatus = new BinaryMaskStatusRequest($recurringPaymentDetails);
        $payment->execute($recurringPaymentStatus);

        if ($recurringPaymentStatus->isSuccess()) {
            //@testo:uncomment:echo 'We are done';
        }

        //@testo:uncomment:echo "Hmm. We are not. Let's check other possible statuses!";
        //@testo:end
        
        $this->assertTrue($recurringPaymentStatus->isFailed());
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
        //@testo:source
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart;
        //@testo:uncomment:use Payum\Paypal\ExpressCheckout\Nvp\Examples\Action\CaptureAwesomeCartAction;
        
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
            //@testo:uncomment:echo 'We are done';
        }

        //@testo:uncomment:echo "Hmm. We are not. Let's check other possible statuses!";
        //@testo:end
        $this->assertTrue($status->isFailed());
        
        $paymentDetails = $status->getModel();
        $this->assertEquals('Security header is not valid', $paymentDetails['L_LONGMESSAGE0']);
    }
}