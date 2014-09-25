<?php
namespace Payum\Klarna\Invoice\Tests;

use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mustNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $config = new Config;

        $payment = PaymentFactory::create($config);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertAttributeCount(13, 'actions', $payment);
    }
}