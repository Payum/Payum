<?php
namespace Payum\Core;

interface PaymentAwareInterface
{
    /**
     * @param \Payum\Core\PaymentInterface $payment
     *
     * @return void
     */
    function setPayment(PaymentInterface $payment);
}