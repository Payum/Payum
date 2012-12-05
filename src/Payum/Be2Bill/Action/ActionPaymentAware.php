<?php
namespace Payum\Be2Bill\Action;

use Payum\Action\ActionPaymentAware as BaseActionPaymentAware;
use Payum\PaymentInterface;
use Payum\Be2Bill\Payment;
use Payum\Exception\InvalidArgumentException;

abstract class ActionPaymentAware extends BaseActionPaymentAware
{
    /**
     * @var \Payum\Be2Bill\Payment
     */
    protected $payment;
    
    public function setPayment(PaymentInterface $payment)
    {
        if (false == $payment instanceof Payment) {
            throw new InvalidArgumentException(sprintf(
                'Invalid payment given. It must be instance of %s but it is given %s',
                'Payum\Be2Bill\Payment',
                get_class($payment)
            ));
        }
        
        parent::setPayment($payment);
    }
}