<?php
namespace Payum\Action;

use Payum\PaymentInterface;

interface ActionPaymentAwareInterface extends ActionInterface
{
    /**
     * @param \Payum\PaymentInterface $payment
     * 
     * @return void
     */
    function setPayment(PaymentInterface $payment);
}