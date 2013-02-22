<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Action\ActionPaymentAware as BaseActionPaymentAware;
use Payum\PaymentInterface;
use Payum\OmnipayBridge\Payment;
use Payum\Exception\InvalidArgumentException;

abstract class ActionPaymentAware extends BaseActionPaymentAware
{
    /**
     * @var \Payum\OmnipayBridge\Payment
     */
    protected $payment;

    /**
     * {@inheritdoc}
     *
     * @throws \Payum\Exception\InvalidArgumentException
     */
    public function setPayment(PaymentInterface $payment)
    {
        if (false == $payment instanceof Payment) {
            throw new InvalidArgumentException(sprintf(
                'Invalid payment given. It must be instance of %s but it is given %s',
                'Payum\OmnipayBridge\Payment',
                get_class($payment)
            ));
        }

        parent::setPayment($payment);
    }
}