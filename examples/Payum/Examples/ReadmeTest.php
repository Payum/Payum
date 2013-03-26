<?php
namespace Payum\Examples;

use Payum\Examples\Request\AuthorizeRequest;
use Payum\Examples\Action\CaptureAction;
use Payum\Extension\StorageExtension;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Examples\Action\AuthorizeAction;
use Payum\Examples\Action\StatusAction;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Payment;
use Payum\Storage\FilesystemStorage;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * @test
     */
    public function bigPicture()
    {
        $this->expectOutputString('We are done!');
        
        //@testo:start
        //@testo:source
        //@testo:uncomment:use Payum\Examples\Action\CaptureAction;
        //@testo:uncomment:use Payum\Examples\Action\StatusAction;
        //@testo:uncomment:use Payum\Request\CaptureRequest;
        //@testo:uncomment:use Payum\Payment;
        
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
        //@testo:source
        //@testo:uncomment:use Payum\Examples\Request\AuthorizeRequest;
        //@testo:uncomment:use Payum\Examples\Action\AuthorizeAction;
        //@testo:uncomment:use Payum\Request\CaptureRequest;
        //@testo:uncomment:use Payum\Request\RedirectUrlInteractiveRequest;
        //@testo:uncomment:use Payum\Payment;

        $payment = new Payment;
        $payment->addAction(new AuthorizeAction());
        
        $request = new AuthorizeRequest($model);
        
        if ($interactiveRequest = $payment->execute($request, $catchInteractive = true)) {    
            if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
                echo 'User must be redirected to '.$interactiveRequest->getUrl();
            }

            //@testo:uncomment:throw $interactiveRequest;
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
        //@testo:source
        //@testo:uncomment:use Payum\Examples\Action\StatusAction;
        //@testo:uncomment:use Payum\Request\BinaryMaskStatusRequest;
        //@testo:uncomment:use Payum\Payment;
        
        //Populate payment with actions.
        $payment = new Payment;
        $payment->addAction(new StatusAction());

        $statusRequest = new BinaryMaskStatusRequest($model);
        $payment->execute($statusRequest);

        //@testo:end
        $this->assertTrue(method_exists($statusRequest, 'isSuccess'));
        $this->assertTrue(method_exists($statusRequest, 'isCanceled'));
        $this->assertTrue(method_exists($statusRequest, 'isFailed'));
        $this->assertTrue(method_exists($statusRequest, 'isPending'));
        $this->assertTrue(method_exists($statusRequest, 'isUnknown'));
        $this->assertTrue(method_exists($statusRequest, 'isNew'));
        //@testo:start
        //Or there is a status which require our attention.
        if ($statusRequest->isSuccess()) {
            echo 'We are done!';
        } 
        
        echo 'Uhh something wrong. Check other possible statuses!';
    }

    /**
     * @test
     */
    public function persistPaymentDetails()
    {
        //@testo:source
        //@testo:uncomment:use Payum\Payment;
        //@testo:uncomment:use Payum\Storage\FilesystemStorage;
        //@testo:uncomment:use Payum\Extension\StorageExtension;

        $storage = new FilesystemStorage('path_to_storage_dir', 'YourModelClass', 'idProperty');

        $payment = new Payment;
        $payment->addExtension(new StorageExtension($storage));
        
        //do capture for example.
    }
}