<?php
namespace Payum\Examples;

use Payum\Examples\Request\AuthorizeRequest;
use Payum\Examples\Action\CaptureAction;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Examples\Action\AuthorizeAction;
use Payum\Examples\Action\StatusAction;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Payment;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * @test
     */
    public function bigPicture()
    {
        $this->expectOutputString('We are done!');
        
        //@testo:start
        //use Payum\Examples\Action\CaptureAction;
        //use Payum\Examples\Action\StatusAction;
        //use Payum\Request\CaptureRequest;
        //use Payum\Payment;
        
        //Populate payment with actions.
        $payment = new Payment;
        $payment->addAction(new CaptureAction());

        //Create request and model. It could be anything supported by an action.
        $captureRequest = new CaptureRequest(array(
            'amount' => 10,
            'currency' => 'EUR'
        ));

        //Execute request
        $payment->execute($captureRequest);
        
        echo 'We are done!';
    }

    /**
     * @test
     */
    public function interactiveRequests()
    {
        $this->expectOutputString('User must be redirected to http://login.thePayment.com');

        $model = array();
        
        //@testo:start        
        //use Payum\Examples\Request\AuthorizeRequest;
        //use Payum\Examples\Action\AuthorizeAction;
        //use Payum\Request\CaptureRequest;
        //use Payum\Request\RedirectUrlInteractiveRequest;
        //use Payum\Payment;

        $payment = new Payment;
        $payment->addAction(new AuthorizeAction());
        
        $request = new AuthorizeRequest($model);
        
        if ($interactiveRequest = $payment->execute($request, $catchInteractive = true)) {    
            if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
                echo 'User must be redirected to '.$interactiveRequest->getUrl();
            }

            //@testo:end
            else {
                //@testo:start
            throw $interactiveRequest;
                //@testo:end
            }
            //@testo:start
        }
    }

    /**
     * @test
     */
    public function gettingRequestStatus()
    {
        $this->expectOutputString('We are done!Uhh something wrong. Check other possible statuses!');

        $model = array();
        
        //@testo:start
        //use Payum\Examples\Action\StatusAction;
        //use Payum\Request\BinaryMaskStatusRequest;
        //use Payum\Payment;
        
        //Populate payment with actions.
        $payment = new Payment;
        $payment->addAction(new StatusAction());

        $statusRequest = new BinaryMaskStatusRequest($model);
        $payment->execute($statusRequest);

        //@testo:end
        $this->assertTrue(method_exists($statusRequest, 'isSuccess'));
        $this->assertTrue(method_exists($statusRequest, 'isCanceled'));
        $this->assertTrue(method_exists($statusRequest, 'isFailed'));
        $this->assertTrue(method_exists($statusRequest, 'isInProgress'));
        $this->assertTrue(method_exists($statusRequest, 'isUnknown'));
        $this->assertTrue(method_exists($statusRequest, 'isNew'));
        //@testo:start
        //Or there is a status which require our attention.
        if ($statusRequest->isSuccess()) {
            echo 'We are done!';
        } 
        
        echo 'Uhh something wrong. Check other possible statuses!';
    }
}