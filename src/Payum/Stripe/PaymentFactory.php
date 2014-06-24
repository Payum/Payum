<?php
namespace Payum\Stripe;

use Payum\Core\Payment;
use Payum\Core\PaymentInterface;
use Payum\Stripe\Js\Action\CaptureAction;
use Payum\Stripe\Js\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @return PaymentInterface
     */
    public static function createJs()
    {
        $payment = new Payment;

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
