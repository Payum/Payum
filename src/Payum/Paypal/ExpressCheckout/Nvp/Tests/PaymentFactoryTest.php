<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $apiMock = $this->createApiMock();

        $payment = PaymentFactory::create($apiMock);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);
        
        $this->assertAttributeCount(1, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}