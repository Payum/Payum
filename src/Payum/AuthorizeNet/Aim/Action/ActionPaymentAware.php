<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionPaymentAware as BaseActionPaymentAware;
use Payum\PaymentInterface;
use Payum\AuthorizeNet\Aim\Payment;
use Payum\Exception\InvalidArgumentException;

abstract class ActionPaymentAware extends BaseActionPaymentAware
{
    /**
     * @var \Payum\AuthorizeNet\Aim\Payment
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
                'Payum\AuthorizeNet\Aim\Payment',
                get_class($payment)
            ));
        }

        parent::setPayment($payment);
    }
}