<?php
namespace Payum\Examples;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * @test
     */
    public function bigPicture()
    {
        $this->expectOutputString('We are done!');
        
        //@testo:start
        //Populate payment with actions.
        $payment = new \Payum\Payment;
        $payment->addAction(new \Payum\Examples\Action\CaptureAction());
        $payment->addAction(new \Payum\Examples\Action\AuthorizeAction());
        $payment->addAction(new \Payum\Examples\Action\StatusAction());

        //Create request object and model. It could be anything supported by an action.
        $captureRequest = new \Payum\Request\CaptureRequest(array(
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
        
        //Populate payment with actions.
        $payment = new \Payum\Payment;
        $payment->addAction(new \Payum\Examples\Action\CaptureAction());
        $payment->addAction(new \Payum\Examples\Action\AuthorizeAction());
        $payment->addAction(new \Payum\Examples\Action\StatusAction());

        //@testo:start
        //...
        
        //Create request object and model. It could be anything supported by an action.
        $authorizeRequest = new \Payum\Examples\Model\AuthorizeRequiredModel(array(
            'amount' => 10,
            'currency' => 'EUR'
        ));
        
        if ($interactiveRequest = $payment->execute(new \Payum\Request\CaptureRequest($authorizeRequest), $isInteractiveRequestExpected = true)) {    
            if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
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
        
        //Populate payment with actions.
        $payment = new \Payum\Payment;
        $payment->addAction(new \Payum\Examples\Action\CaptureAction());
        $payment->addAction(new \Payum\Examples\Action\AuthorizeAction());
        $payment->addAction(new \Payum\Examples\Action\StatusAction());

        //Create a sell object. It could be anything supported by an action.
        $sell = new \Payum\Examples\Model\TestModel;
        $sell->setPrice(100.05);
        $sell->setCurrency('EUR');

        //@testo:start
        //...

        $statusRequest = new \Payum\Request\BinaryMaskStatusRequest($sell);
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