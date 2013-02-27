<?php
namespace Payum\OmnipayBridge\Tests\Integration;

use Omnipay\Dummy\Gateway;
use Payum\OmnipayBridge\PaymentFactory;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFinishSuccessfully()
    {
        $payment = PaymentFactory::create(new Gateway());

        $date = new \DateTime('now + 2 year');
        
        $captureRequest = new CaptureRequest(array(
            'amount' => 1000,
            'card' => array(
                'number' => '5555556778250000', //end zero so will be accepted
                'cvv' => 123,
                'expiryMonth' => 6,
                'expiryYear' => $date->format('y'),
                'firstName' => 'foo',
                'lastName' => 'bar',
            )
        ));
        
        $payment->execute($captureRequest);
        
        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payment->execute($statusRequest);
        
        $this->assertTrue($statusRequest->isSuccess());
    }

    /**
     * @test
     */
    public function shouldFinishWithFailed()
    {
        $this->markTestIncomplete('The DummyGateway buggy at the moment');
        
        $payment = PaymentFactory::create(new Gateway());

        $captureRequest = new CaptureRequest(array(
            'amount' => 1000,
            'card' => array(
                'number' => '5555557730105001', //ends one so will be declined
                'cvv' => 123,
                'expiryMonth' => 6,
                'expiryYear' => (new \DateTime('now + 2 year'))->format('y'),
                'firstName' => 'foo',
                'lastName' => 'bar',
            )
        ));

        $payment->execute($captureRequest);

        $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
        $payment->execute($statusRequest);

        $this->assertTrue($statusRequest->isFailed());
    }
}