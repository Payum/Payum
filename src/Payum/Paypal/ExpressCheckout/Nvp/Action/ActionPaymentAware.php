<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionPaymentAware as BaseActionPaymentAware;
use Payum\PaymentInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Payment;
use Payum\Exception\InvalidArgumentException;

abstract class ActionPaymentAware extends BaseActionPaymentAware
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Payment
     */
    protected $payment;
    
    public function setPayment(PaymentInterface $payment)
    {
        if (false == $payment instanceof Payment) {
            throw new InvalidArgumentException(sprintf(
                'Invalid payment given. It must be instance of %s but it is given %s',
                'Payum\Paypal\ExpressCheckout\Nvp\Payment',
                get_class($payment)
            ));
        }
        
        parent::setPayment($payment);
    }
}