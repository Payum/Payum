<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createApi()
    {
        //@testo:start
        $api = new \Payum\Paypal\ExpressCheckout\Nvp\Api(new \Buzz\Client\Curl, array(
            'username' => 'a_username',
            'password' => 'a_pasword',
            'signature' => 'a_signature',
            'return_url' => 'a_return_url',
            'cancel_url' => 'a_return_url',
            'sandbox' => true
        ));
        //@testo:end
        
        return $api;
    }
    
    /**
     * @test
     * 
     * @depends createApi
     */
    public function createPayment(\Payum\Paypal\ExpressCheckout\Nvp\Api $api)
    {
        //@testo:start
        //...
        
        $payment = new \Payum\Paypal\ExpressCheckout\Nvp\Payment();
        
        $payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SetExpressCheckoutAction($api));
        $payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\GetExpressCheckoutDetailsAction($api));
        $payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction($api));
        $payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SaleAction());
        $payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SimpleSellAction());
        $payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction());
        //@testo:end
        
        return $payment;
    }

    /**
     * @test
     *
     * @depends createPayment
     */
    public function doSell(\Payum\Paypal\ExpressCheckout\Nvp\Payment $payment)
    {
        //...
        
        $sell = new \Payum\Request\SimpleSellRequest();
        $sell->setPrice(100);
        $sell->setCurrency('USD');

        if ($interactiveRequest = $payment->execute($sell)) {
            if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
                echo 'Paypal requires the user be redirected to: '.$interactiveRequest->getUrl();
            }
        }

        $statusRequest = new \Payum\Request\BinaryMaskStatusRequest($sell);
        $payment->execute($statusRequest);
        if ($statusRequest->isSuccess()) {
            //We are done!
        } else if ($statusRequest->isCanceled()) {
            //Canceled!
        } elseif ($statusRequest->isFailed()) {
            //Failed
        } elseif ($statusRequest->isInProgress()) {
            //In progress!
        }
    }
}