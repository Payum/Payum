<?php
namespace Payum\OmnipayBridge\Tests\Integration;

use Omnipay\Dummy\Gateway;

use Payum\OmnipayBridge\OnsitePaymentFactory;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\CaptureRequest;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFinishSuccessfully()
    {
        $payment = OnsitePaymentFactory::create(new Gateway());

        $date = new \DateTime('now + 2 year');

        $captureRequest = new CaptureRequest(array(
            'amount' => '1000.00',
            'card' => array(
                'number' => '4242424242424242', // must be authorized
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
        $payment = OnsitePaymentFactory::create(new Gateway());

        $date = new \DateTime('now + 2 year');

        $captureRequest = new CaptureRequest(array(
            'amount' => '1000.00',
            'card' => array(
                'number' => '4111111111111111', //must be declined,
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

        $this->assertTrue($statusRequest->isFailed());
    }
}
