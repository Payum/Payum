<?php
namespace Payum;

interface PaymentAwareInterface
{
    /**
     * @param \Payum\PaymentInterface $payment
     *
     * @return void
     */
    function setPayment(PaymentInterface $payment);
}