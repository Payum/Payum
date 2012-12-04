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

        //Create request object. It could be anything supported by an action.
        $sell = new \Payum\Domain\SimpleSell;
        $sell->setPrice(100.05);
        $sell->setCurrency('EUR');

        //Execute request
        if (null === $payment->execute(new \Payum\Request\CaptureRequest($sell))) {
            echo 'We are done!';
        }
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
        
        //Create authorize required request.
        $sell = new \Payum\Examples\Model\AuthorizeRequiredSell();
        $sell->setPrice(100.05);
        $sell->setCurrency('EUR');
        
        if ($interactiveRequest = $payment->execute(new \Payum\Request\CaptureRequest($sell))) {    
            if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
                echo 'User must be redirected to '.$interactiveRequest->getUrl();
            } 
            
            //...
        }
    }

    /**
     * @test
     */
    public function gettingRequestStatus()
    {
        $this->expectOutputString('We are done!');
        
        //Populate payment with actions.
        $payment = new \Payum\Payment;
        $payment->addAction(new \Payum\Examples\Action\CaptureAction());
        $payment->addAction(new \Payum\Examples\Action\AuthorizeAction());
        $payment->addAction(new \Payum\Examples\Action\StatusAction());

        //Create a sell object. It could be anything supported by an action.
        $sell = new \Payum\Domain\SimpleSell;
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
        } else if ($statusRequest->isCanceled()) {
            echo 'Canceled!';
        } elseif ($statusRequest->isFailed()) {
            echo 'Failed!';
        } elseif ($statusRequest->isInProgress()) {
            echo 'In progress!';
        } elseif ($statusRequest->isUnknown()) {
            echo 'Unknown!';
        } elseif ($statusRequest->isNew()) {
           echo 'New!';
        }
    }
}